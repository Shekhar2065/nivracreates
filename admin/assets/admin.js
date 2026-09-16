document.querySelectorAll('[data-confirm]').forEach((item) => {
  item.addEventListener('click', (event) => {
    if (!window.confirm(item.dataset.confirm || 'Are you sure?')) event.preventDefault();
  });
});

document.querySelectorAll('[data-gallery-order-manager]').forEach((manager) => {
  let pointerDraggedRow = null;

  function rows() {
    return Array.from(manager.querySelectorAll('[data-gallery-row]'));
  }

  function syncPositions() {
    rows().forEach((row, index) => {
      const position = String(index + 1);
      const input = row.querySelector('[data-gallery-position]');
      const label = row.querySelector('[data-gallery-position-label]');
      const handle = row.querySelector('[data-drag-handle]');
      if (input) input.value = position;
      if (label) label.textContent = position;
      if (handle) handle.setAttribute('aria-label', `Drag image at position ${position}`);
    });
  }

  function moveRowAtPoint(row, clientX, clientY) {
    const target = document.elementFromPoint(clientX, clientY)?.closest('[data-gallery-row]');
    if (!target || target === row || target.parentElement !== manager) return;
    const targetBox = target.getBoundingClientRect();
    manager.insertBefore(row, clientY < targetBox.top + targetBox.height / 2 ? target : target.nextSibling);
    syncPositions();
  }

  manager.querySelectorAll('[data-drag-handle]').forEach((handle) => {
    handle.addEventListener('pointerdown', (event) => {
      pointerDraggedRow = handle.closest('[data-gallery-row]');
      pointerDraggedRow?.classList.add('is-dragging');
      handle.setAttribute('aria-grabbed', 'true');
      handle.setPointerCapture(event.pointerId);
      event.preventDefault();
    });

    handle.addEventListener('pointermove', (event) => {
      if (!pointerDraggedRow) return;
      moveRowAtPoint(pointerDraggedRow, event.clientX, event.clientY);
      event.preventDefault();
    });

    function finishPointerDrag() {
      pointerDraggedRow?.classList.remove('is-dragging');
      pointerDraggedRow = null;
      handle.setAttribute('aria-grabbed', 'false');
      syncPositions();
    }
    handle.addEventListener('pointerup', finishPointerDrag);
    handle.addEventListener('pointercancel', finishPointerDrag);

    handle.addEventListener('keydown', (event) => {
      const row = handle.closest('[data-gallery-row]');
      if (!row || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;
      event.preventDefault();
      if (event.key === 'ArrowUp' && row.previousElementSibling) {
        manager.insertBefore(row, row.previousElementSibling);
      } else if (event.key === 'ArrowDown' && row.nextElementSibling) {
        manager.insertBefore(row.nextElementSibling, row);
      }
      syncPositions();
    });
  });

  syncPositions();
});

const settingsTabs = document.querySelector('[data-settings-tabs]');
const settingsForm = document.querySelector('[data-settings-form]');

if (settingsTabs && settingsForm) {
  const tabs = Array.from(settingsTabs.querySelectorAll('[data-settings-tab]'));
  const panels = Array.from(settingsForm.querySelectorAll('[data-settings-panel]'));
  const returnTab = settingsForm.querySelector('[data-settings-return-tab]');
  const saveStatus = settingsForm.querySelector('[data-settings-save-status]');
  let isDirty = false;

  function activateSettingsTab(tabKey, updateHash = true) {
    const selectedTab = tabs.some((tab) => tab.dataset.settingsTab === tabKey) ? tabKey : 'brand';
    tabs.forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.settingsTab === selectedTab)));
    panels.forEach((panel) => { panel.hidden = panel.dataset.settingsPanel !== selectedTab; });
    if (returnTab) returnTab.value = selectedTab;
    if (updateHash) window.history.replaceState(null, '', `#settings-${selectedTab}`);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => activateSettingsTab(tab.dataset.settingsTab || 'brand'));
  });

  const initialTab = window.location.hash.startsWith('#settings-')
    ? window.location.hash.replace('#settings-', '')
    : 'brand';
  activateSettingsTab(initialTab, false);

  settingsForm.querySelectorAll('[data-settings-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files?.[0];
      const previewId = input.dataset.previewId;
      const preview = previewId ? document.getElementById(previewId) : null;
      if (!file || !preview) return;
      preview.src = URL.createObjectURL(file);
      preview.hidden = false;
      const placeholder = settingsForm.querySelector(`[data-placeholder-for="${previewId}"]`);
      if (placeholder) placeholder.hidden = true;
    });
  });

  function markSettingsDirty() {
    isDirty = true;
    if (saveStatus) {
      saveStatus.textContent = 'Unsaved changes';
      saveStatus.classList.add('is-dirty');
    }
  }

  settingsForm.addEventListener('input', markSettingsDirty);
  settingsForm.addEventListener('change', markSettingsDirty);
  settingsForm.addEventListener('submit', () => {
    isDirty = false;
    if (saveStatus) {
      saveStatus.textContent = 'Saving…';
      saveStatus.classList.remove('is-dirty');
    }
  });

  window.addEventListener('beforeunload', (event) => {
    if (!isDirty) return;
    event.preventDefault();
    event.returnValue = '';
  });
}
