<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$db = admin_db();
admin_require_auth();

$syncProjectCover = static function (PDO $connection, int $projectId): void {
    $coverStatement = $connection->prepare('SELECT image_path FROM project_images WHERE project_id = ? ORDER BY display_order, id LIMIT 1');
    $coverStatement->execute([$projectId]);
    $coverImage = $coverStatement->fetchColumn();
    if (is_string($coverImage) && $coverImage !== '') {
        $connection->prepare('UPDATE projects SET image = ? WHERE id = ?')->execute([$coverImage, $projectId]);
    }
};

$saveGalleryDetails = static function (PDO $connection, int $projectId, array $orders, array $altTexts): void {
    $imageIdsStatement = $connection->prepare('SELECT id FROM project_images WHERE project_id = ? ORDER BY display_order, id');
    $imageIdsStatement->execute([$projectId]);
    $imageIds = array_map('intval', $imageIdsStatement->fetchAll(PDO::FETCH_COLUMN));
    if ($imageIds === []) {
        return;
    }

    $positions = [];
    foreach ($imageIds as $imageId) {
        if (!array_key_exists($imageId, $orders)) {
            throw new RuntimeException('Every gallery image needs a position.');
        }
        $position = (int) $orders[$imageId];
        if ($position < 1 || $position > count($imageIds)) {
            throw new RuntimeException('Choose a valid position for every gallery image.');
        }
        $positions[] = $position;
    }
    if (count(array_unique($positions)) !== count($positions)) {
        throw new RuntimeException('Each gallery image must have a different position.');
    }

    $update = $connection->prepare('UPDATE project_images SET display_order = ?, alt_text = ? WHERE id = ? AND project_id = ?');
    foreach ($imageIds as $imageId) {
        $altText = mb_substr(trim((string) ($altTexts[$imageId] ?? '')), 0, 255);
        $update->execute([(int) $orders[$imageId], $altText, $imageId, $projectId]);
    }

    $coverStatement = $connection->prepare('SELECT image_path FROM project_images WHERE project_id = ? ORDER BY display_order, id LIMIT 1');
    $coverStatement->execute([$projectId]);
    $coverImage = $coverStatement->fetchColumn();
    if (is_string($coverImage) && $coverImage !== '') {
        $connection->prepare('UPDATE projects SET image = ? WHERE id = ?')->execute([$coverImage, $projectId]);
    }
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $action = (string) ($_POST['action'] ?? 'save_project');
    $id = (int) ($_POST['id'] ?? 0);
    $deleteImageId = 0;
    if (str_starts_with($action, 'delete_gallery_image:')) {
        $deleteImageId = (int) substr($action, strlen('delete_gallery_image:'));
        $action = 'delete_gallery_image';
    }

    try {
        if ($action === 'delete' && $id > 0) {
            $db->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
            admin_flash('success', 'Project deleted.');
            admin_redirect('projects.php');
        }

        if (in_array($action, ['upload_gallery', 'save_gallery', 'delete_gallery_image'], true) && $id <= 0) {
            throw new RuntimeException('Save the project before managing its gallery.');
        }

        if ($action === 'upload_gallery') {
            $paths = admin_upload_images('gallery_images');
            if ($paths === []) {
                throw new RuntimeException('Choose at least one gallery image to upload.');
            }

            $titleStatement = $db->prepare('SELECT title FROM projects WHERE id = ?');
            $titleStatement->execute([$id]);
            $projectTitle = (string) ($titleStatement->fetchColumn() ?: 'Project');
            $orderStatement = $db->prepare('SELECT COALESCE(MAX(display_order), 0) FROM project_images WHERE project_id = ?');
            $orderStatement->execute([$id]);
            $displayOrder = (int) $orderStatement->fetchColumn();
            $insert = $db->prepare('INSERT INTO project_images (project_id, image_path, alt_text, display_order) VALUES (?, ?, ?, ?)');

            foreach ($paths as $path) {
                $displayOrder++;
                $insert->execute([$id, $path, $projectTitle . ' gallery image', $displayOrder]);
            }
            $syncProjectCover($db, $id);

            admin_flash('success', count($paths) === 1 ? 'Gallery image uploaded.' : count($paths) . ' gallery images uploaded.');
            admin_redirect('projects.php?edit=' . $id);
        }

        if ($action === 'delete_gallery_image') {
            $imageId = $deleteImageId;
            if ($imageId <= 0) {
                throw new RuntimeException('Choose an image to remove.');
            }
            $countStatement = $db->prepare('SELECT COUNT(*) FROM project_images WHERE project_id = ?');
            $countStatement->execute([$id]);
            if ((int) $countStatement->fetchColumn() <= 1) {
                throw new RuntimeException('Upload another image before removing the last gallery image.');
            }
            $delete = $db->prepare('DELETE FROM project_images WHERE id = ? AND project_id = ?');
            $delete->execute([$imageId, $id]);
            $syncProjectCover($db, $id);
            admin_flash('success', 'Image removed from the project gallery.');
            admin_redirect('projects.php?edit=' . $id);
        }

        if ($action === 'save_gallery') {
            $orders = is_array($_POST['image_order'] ?? null) ? $_POST['image_order'] : [];
            $altTexts = is_array($_POST['image_alt'] ?? null) ? $_POST['image_alt'] : [];

            $db->beginTransaction();
            $saveGalleryDetails($db, $id, $orders, $altTexts);
            $db->commit();

            admin_flash('success', 'Gallery order and descriptions saved.');
            admin_redirect('projects.php?edit=' . $id);
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        if ($title === '' || $slug === '') {
            throw new RuntimeException('Title and slug are required.');
        }

        $existingProject = null;
        if ($id > 0) {
            $existingStatement = $db->prepare('SELECT image, logo_image, project_year, verified_result, display_order FROM projects WHERE id = ?');
            $existingStatement->execute([$id]);
            $existingProject = $existingStatement->fetch() ?: null;
        }
        $newGalleryPaths = admin_upload_images('gallery_images');
        $image = (string) ($existingProject['image'] ?? ($newGalleryPaths[0] ?? ''));
        if ($image === '') {
            throw new RuntimeException('Choose at least one project image.');
        }
        if ($existingProject) {
            $projectDisplayOrder = (int) $existingProject['display_order'];
        } else {
            $projectDisplayOrder = (int) $db->query('SELECT COALESCE(MAX(display_order), 0) + 1 FROM projects')->fetchColumn();
        }
        $existingLogo = (string) ($existingProject['logo_image'] ?? '');
        $logoImage = isset($_POST['remove_logo']) ? null : admin_upload_image('logo_upload', $existingLogo);

        $data = [
            $title,
            $slug,
            trim((string) ($_POST['client_name'] ?? '')),
            trim((string) ($_POST['industry'] ?? '')),
            trim((string) ($_POST['services'] ?? '')),
            trim((string) ($_POST['short_description'] ?? '')),
            $image,
            $logoImage,
            (string) ($existingProject['project_year'] ?? ''),
            $existingProject['verified_result'] ?? null,
            null,
            null,
            isset($_POST['is_published']) ? 1 : 0,
            $projectDisplayOrder,
        ];

        if ($id > 0) {
            $statement = $db->prepare('UPDATE projects SET title=?,slug=?,client_name=?,industry=?,services=?,short_description=?,image=?,logo_image=?,project_year=?,verified_result=?,project_url=?,project_cta=?,is_published=?,display_order=? WHERE id=?');
            $data[] = $id;
        } else {
            $statement = $db->prepare('INSERT INTO projects (title,slug,client_name,industry,services,short_description,image,logo_image,project_year,verified_result,project_url,project_cta,is_published,display_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        }
        $statement->execute($data);

        $savedId = $id > 0 ? $id : (int) $db->lastInsertId();
        if ($id > 0 && is_array($_POST['image_order'] ?? null)) {
            $saveGalleryDetails(
                $db,
                $id,
                $_POST['image_order'],
                is_array($_POST['image_alt'] ?? null) ? $_POST['image_alt'] : []
            );
        }

        if ($newGalleryPaths !== []) {
            $orderStatement = $db->prepare('SELECT COALESCE(MAX(display_order), 0) FROM project_images WHERE project_id = ?');
            $orderStatement->execute([$savedId]);
            $displayOrder = (int) $orderStatement->fetchColumn();
            $insertImage = $db->prepare('INSERT INTO project_images (project_id, image_path, alt_text, display_order) VALUES (?, ?, ?, ?)');
            foreach ($newGalleryPaths as $path) {
                $displayOrder++;
                $insertImage->execute([$savedId, $path, $title . ' gallery image', $displayOrder]);
            }
            $syncProjectCover($db, $savedId);
        }

        $testimonialText = trim((string) ($_POST['testimonial_text'] ?? ''));
        $testimonialStatement = $db->prepare('SELECT id FROM testimonials WHERE project_id = ? ORDER BY display_order, id LIMIT 1');
        $testimonialStatement->execute([$savedId]);
        $testimonialId = (int) ($testimonialStatement->fetchColumn() ?: 0);

        if (isset($_POST['remove_testimonial'])) {
            $db->prepare('DELETE FROM testimonials WHERE project_id = ?')->execute([$savedId]);
        } elseif ($testimonialText !== '') {
            $testimonialClient = trim((string) ($_POST['testimonial_client_name'] ?? '')) ?: (trim((string) ($_POST['client_name'] ?? '')) ?: $title);
            $testimonialCompany = trim((string) ($_POST['testimonial_company_name'] ?? '')) ?: $testimonialClient;
            $testimonialRole = trim((string) ($_POST['testimonial_client_role'] ?? '')) ?: 'Client';
            $testimonialPublished = isset($_POST['testimonial_published']) ? 1 : 0;

            if ($testimonialId > 0) {
                $testimonialUpdate = $db->prepare('UPDATE testimonials SET client_name = ?, company_name = ?, client_role = ?, testimonial = ?, is_published = ?, display_order = ? WHERE id = ?');
                $testimonialUpdate->execute([$testimonialClient, $testimonialCompany, $testimonialRole, $testimonialText, $testimonialPublished, $projectDisplayOrder, $testimonialId]);
                $db->prepare('UPDATE testimonials SET is_published = 0 WHERE project_id = ? AND id <> ?')->execute([$savedId, $testimonialId]);
            } else {
                $testimonialInsert = $db->prepare('INSERT INTO testimonials (client_name, company_name, client_role, testimonial, project_id, is_published, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $testimonialInsert->execute([$testimonialClient, $testimonialCompany, $testimonialRole, $testimonialText, $savedId, $testimonialPublished, $projectDisplayOrder]);
            }
        }

        admin_flash('success', $id > 0 ? 'Project updated.' : 'Project added. You can now build its image gallery.');
        admin_redirect('projects.php?edit=' . $savedId);
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $message = $exception instanceof PDOException ? 'Could not save. Make sure the project slug is unique.' : $exception->getMessage();
        admin_flash('error', $message);
        admin_redirect('projects.php' . ($id > 0 ? '?edit=' . $id : ''));
    }
}

$edit = null;
if ((int) ($_GET['edit'] ?? 0) > 0) {
    $statement = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $statement->execute([(int) $_GET['edit']]);
    $edit = $statement->fetch() ?: null;
}

$projects = $db->query('SELECT projects.*, (SELECT COUNT(*) FROM project_images WHERE project_images.project_id = projects.id) AS gallery_count FROM projects ORDER BY display_order, id')->fetchAll();
$galleryImages = [];
$projectTestimonial = null;
if ($edit) {
    $statement = $db->prepare('SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order, id');
    $statement->execute([(int) $edit['id']]);
    $galleryImages = $statement->fetchAll();
    $statement = $db->prepare('SELECT * FROM testimonials WHERE project_id = ? ORDER BY display_order, id LIMIT 1');
    $statement->execute([(int) $edit['id']]);
    $projectTestimonial = $statement->fetch() ?: null;
}

$v = $edit ?: [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'client_name' => '',
    'industry' => '',
    'services' => '',
    'short_description' => '',
    'image' => '',
    'logo_image' => '',
    'project_year' => '',
    'verified_result' => '',
    'is_published' => 1,
    'display_order' => 0,
];
$testimonialValues = $projectTestimonial ?: [
    'client_name' => (string) ($v['client_name'] ?? ''),
    'company_name' => (string) ($v['client_name'] ?? ''),
    'client_role' => '',
    'testimonial' => '',
    'is_published' => 1,
];

admin_header('Projects', 'projects');
?>
<style>
.project-gallery-manager{margin-top:12px;border-top:1px solid var(--line);padding-top:20px}
.project-image-list{display:grid;gap:10px;margin-top:14px}
.project-image-row{display:grid;grid-template-columns:38px 140px minmax(0,1fr) auto;gap:14px;align-items:center;border:1px solid var(--line);border-radius:12px;background:var(--paper);padding:10px;transition:border-color .15s,box-shadow .15s,opacity .15s}
.project-image-row.is-dragging{opacity:.55;border-color:var(--ink);box-shadow:0 12px 30px rgba(6,47,75,.14)}
.project-drag-handle{display:grid;place-items:center;width:38px;height:58px;border:0;border-radius:8px;background:#e6ebe6;color:var(--ink);font-size:22px;cursor:grab;touch-action:none}.project-drag-handle:active{cursor:grabbing}
.project-image-preview{position:relative;height:90px;overflow:hidden;border-radius:8px;background:#dfe6e1}
.project-image-preview img{width:100%;height:100%;object-fit:cover}
.project-image-preview strong{position:absolute;left:8px;top:8px;border-radius:999px;background:var(--acid);color:var(--ink);padding:5px 9px;font-size:11px;text-transform:uppercase}
.project-gallery-upload{margin-top:22px;border-top:1px solid var(--line);padding-top:20px}
@media(max-width:720px){.project-image-row{grid-template-columns:34px 92px minmax(0,1fr)}.project-drag-handle{width:34px}.project-image-row>.button{grid-column:2/-1;justify-self:start}.project-image-preview{height:72px}}
</style>
<div class="split">
    <section class="panel">
        <div class="panel-head">
            <h2><?= $edit ? 'Edit project' : 'Add a project' ?></h2>
            <?php if ($edit): ?><a href="projects.php">Cancel</a><?php endif; ?>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>">
            <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
            <div class="grid-2">
                <div class="field"><label>Title</label><input name="title" required value="<?= admin_e((string) $v['title']) ?>"></div>
                <div class="field"><label>URL slug</label><input name="slug" required value="<?= admin_e((string) $v['slug']) ?>"></div>
                <div class="field"><label>Client</label><input name="client_name" value="<?= admin_e((string) $v['client_name']) ?>"></div>
                <div class="field"><label>Industry</label><input name="industry" value="<?= admin_e((string) $v['industry']) ?>"></div>
                <div class="field full"><label>Services</label><input name="services" value="<?= admin_e((string) $v['services']) ?>"></div>
                <div class="field full"><label>Description</label><textarea name="short_description"><?= admin_e((string) $v['short_description']) ?></textarea></div>
                <div class="field full">
                    <label for="logo_upload">Project logo (optional)</label>
                    <?php if (!empty($v['logo_image'])): ?>
                        <div style="display:flex;align-items:center;gap:16px;margin-bottom:10px">
                            <img src="../<?= admin_e((string) $v['logo_image']) ?>" alt="" style="width:100px;height:70px;object-fit:contain;border:1px solid var(--line);border-radius:8px;padding:8px;background:var(--paper)">
                            <label style="font-weight:600;letter-spacing:0;text-transform:none"><input type="checkbox" name="remove_logo" value="1" style="width:auto"> Remove current logo</label>
                        </div>
                    <?php endif; ?>
                    <input id="logo_upload" type="file" name="logo_upload" accept="image/jpeg,image/png,image/webp">
                    <small>Upload a transparent PNG or WebP for the best result · maximum 5 MB.</small>
                </div>
                <div class="field full project-gallery-manager">
                    <div class="panel-head" style="margin:8px 0 4px">
                        <div>
                            <h2>Project images</h2>
                            <small><?= $edit ? 'Drag images using the handle to choose their website order. The first image is the cover.' : 'Choose the pictures for this project. You can arrange them after creating the project.' ?></small>
                        </div>
                        <?php if ($edit): ?><span class="muted"><?= count($galleryImages) ?> images</span><?php endif; ?>
                    </div>

                    <?php if ($edit && !$galleryImages): ?>
                        <div class="empty">No project images yet. Upload pictures below.</div>
                    <?php elseif ($edit): ?>
                        <div class="project-image-list" data-gallery-order-manager>
                            <?php foreach ($galleryImages as $galleryIndex => $galleryImage): ?>
                                <?php $imagePosition = $galleryIndex + 1; ?>
                                <article class="project-image-row" data-gallery-row>
                                    <button class="project-drag-handle" type="button" data-drag-handle aria-label="Drag image at position <?= $imagePosition ?>" aria-grabbed="false">↕</button>
                                    <div class="project-image-preview">
                                        <img src="../<?= admin_e((string) $galleryImage['image_path']) ?>" alt="">
                                        <strong data-gallery-position-label><?= $imagePosition ?></strong>
                                    </div>
                                    <div class="field">
                                        <label for="image_alt_<?= (int) $galleryImage['id'] ?>">Image description</label>
                                        <input id="image_alt_<?= (int) $galleryImage['id'] ?>" name="image_alt[<?= (int) $galleryImage['id'] ?>]" value="<?= admin_e((string) $galleryImage['alt_text']) ?>">
                                        <input type="hidden" name="image_order[<?= (int) $galleryImage['id'] ?>]" value="<?= $imagePosition ?>" data-gallery-position>
                                    </div>
                                    <button class="button danger small" type="submit" name="action" value="delete_gallery_image:<?= (int) $galleryImage['id'] ?>" data-confirm="Delete this image from the project?">Delete</button>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <div class="actions"><button class="button secondary" type="submit" name="action" value="save_gallery">Save image order</button></div>
                    <?php endif; ?>

                    <div class="field project-gallery-upload">
                        <label for="gallery_images"><?= $edit ? 'Upload more pictures' : 'Choose project pictures' ?></label>
                        <input id="gallery_images" type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" multiple <?= $edit ? '' : 'required' ?>>
                        <small>Select one or several JPG, PNG, or WebP files · maximum 5 MB each.</small>
                        <?php if ($edit): ?><div class="actions"><button class="button acid" type="submit" name="action" value="upload_gallery">Upload pictures</button></div><?php endif; ?>
                    </div>
                </div>
                <div class="field full" style="margin-top:12px;border-top:1px solid var(--line);padding-top:20px">
                    <div class="panel-head" style="margin:8px 0 4px">
                        <div>
                            <h2>Client testimonial</h2>
                            <small>Optional. This testimonial appears inside this project’s public card.</small>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="field"><label for="testimonial_client_name">Client name</label><input id="testimonial_client_name" name="testimonial_client_name" value="<?= admin_e((string) $testimonialValues['client_name']) ?>"></div>
                        <div class="field"><label for="testimonial_company_name">Company</label><input id="testimonial_company_name" name="testimonial_company_name" value="<?= admin_e((string) $testimonialValues['company_name']) ?>"></div>
                        <div class="field"><label for="testimonial_client_role">Client role</label><input id="testimonial_client_role" name="testimonial_client_role" value="<?= admin_e((string) $testimonialValues['client_role']) ?>"></div>
                        <div class="field full"><label for="testimonial_text">Testimonial</label><textarea id="testimonial_text" name="testimonial_text"><?= admin_e((string) $testimonialValues['testimonial']) ?></textarea></div>
                        <div class="field full" style="display:flex;flex-direction:row;flex-wrap:wrap;gap:20px">
                            <label style="font-weight:600;letter-spacing:0;text-transform:none"><input type="checkbox" name="testimonial_published" value="1" style="width:auto" <?= !empty($testimonialValues['is_published']) ? 'checked' : '' ?>> Show testimonial on website</label>
                            <?php if ($projectTestimonial): ?><label style="font-weight:600;letter-spacing:0;text-transform:none;color:var(--danger)"><input type="checkbox" name="remove_testimonial" value="1" style="width:auto"> Remove testimonial</label><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="field full"><label><input type="checkbox" name="is_published" value="1" style="width:auto" <?= !empty($v['is_published']) ? 'checked' : '' ?>> Published on website</label></div>
            </div>
            <div class="actions"><button class="button acid" type="submit" name="action" value="save_project"><?= $edit ? 'Save project' : 'Add project' ?></button></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>All projects</h2><span class="muted"><?= count($projects) ?> total</span></div>
        <?php if (!$projects): ?>
            <div class="empty">No projects yet.</div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div style="display:grid;grid-template-columns:70px 1fr auto;gap:12px;align-items:center;padding:12px 0;border-bottom:1px solid var(--line)">
                    <img class="thumb" src="../<?= admin_e((string) $project['image']) ?>" alt="">
                    <div>
                        <strong><?= admin_e((string) $project['title']) ?></strong><br>
                        <span class="status <?= $project['is_published'] ? 'published' : '' ?>"><?= $project['is_published'] ? 'Published' : 'Draft' ?></span>
                        <small style="display:block;margin-top:4px"><?= (int) $project['gallery_count'] ?> gallery images</small>
                    </div>
                    <div class="actions" style="margin:0">
                        <a class="button secondary small" href="?edit=<?= (int) $project['id'] ?>">Edit</a>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= admin_csrf() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
                            <button class="button danger small" data-confirm="Delete this project and its gallery?" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<?php admin_footer(); ?>
