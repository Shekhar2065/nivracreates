(function () {
    'use strict';

    document.documentElement.classList.add('js-enabled');

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const siteHeader = document.querySelector('.site-header');
    const selectedWorkSection = document.querySelector('.work-section');
    const menuButton = document.querySelector('#menu-toggle');
    const mobileMenu = document.querySelector('#mobile-menu');
    const mobileLinks = Array.from(document.querySelectorAll('.mobile-link'));

    function setMenu(open) {
        if (!menuButton || !mobileMenu) return;
        if (open) setHeaderHidden(false);
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        mobileMenu.setAttribute('aria-hidden', String(!open));
        mobileMenu.classList.toggle('is-open', open);
        document.body.classList.toggle('menu-open', open);
        if (open && mobileLinks[0]) mobileLinks[0].focus();
    }

    function setHeaderHidden(hidden) {
        if (!siteHeader) return;
        siteHeader.classList.toggle('is-hidden', hidden);
        document.body.classList.toggle('nav-hidden', hidden);
    }

    menuButton?.addEventListener('click', () => setMenu(menuButton.getAttribute('aria-expanded') !== 'true'));
    mobileLinks.forEach((link) => link.addEventListener('click', (event) => {
        const hash = link.getAttribute('href');
        const target = hash?.startsWith('#') ? document.querySelector(hash) : null;
        if (!target) {
            setMenu(false);
            return;
        }

        event.preventDefault();
        setMenu(false);
        setHeaderHidden(true);

        window.requestAnimationFrame(() => {
            const destination = Math.max(0, window.scrollY + target.getBoundingClientRect().top);

            window.history.pushState(null, '', hash);
            window.scrollTo({
                top: destination,
                behavior: reducedMotion ? 'auto' : 'smooth',
            });
        });
    }));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuButton?.getAttribute('aria-expanded') === 'true') {
            setMenu(false);
            menuButton.focus();
        }
    });

    const btsOpenButtons = Array.from(document.querySelectorAll('[data-bts-open]'));
    const btsCloseButtons = Array.from(document.querySelectorAll('[data-bts-close]'));
    let btsReturnFocus = null;
    let activeBtsModal = null;
    let activeBtsDialog = null;

    function openBtsModal(trigger) {
        const modalId = trigger.dataset.btsOpen;
        const modal = modalId ? document.querySelector(`#${modalId}`) : null;
        const dialog = modal?.querySelector('.bts-dialog');
        if (!modal || !dialog) return;
        if (activeBtsModal && activeBtsModal !== modal) {
            activeBtsModal.classList.remove('is-open');
            activeBtsModal.setAttribute('aria-hidden', 'true');
        }
        btsReturnFocus = trigger;
        activeBtsModal = modal;
        activeBtsDialog = dialog;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('bts-open');
        window.setTimeout(() => dialog.focus(), 50);
    }

    function closeBtsModal() {
        if (!activeBtsModal) return;
        activeBtsModal.classList.remove('is-open');
        activeBtsModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('bts-open');
        activeBtsModal = null;
        activeBtsDialog = null;
        btsReturnFocus?.focus();
    }

    btsOpenButtons.forEach((button) => button.addEventListener('click', () => openBtsModal(button)));
    btsCloseButtons.forEach((button) => button.addEventListener('click', closeBtsModal));
    document.addEventListener('keydown', (event) => {
        if (!activeBtsModal?.classList.contains('is-open')) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeBtsModal();
            return;
        }
        if (event.key === 'Tab' && activeBtsDialog) {
            const focusable = Array.from(activeBtsDialog.querySelectorAll('button, a, [tabindex]:not([tabindex="-1"])'));
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    const progress = document.querySelector('#scroll-progress');
    let ticking = false;
    let lastNavPosition = window.scrollY;
    function updateProgress() {
        const total = document.documentElement.scrollHeight - window.innerHeight;
        const amount = total > 0 ? window.scrollY / total : 0;
        if (progress) progress.style.transform = `scaleX(${Math.min(1, Math.max(0, amount))})`;

        const currentPosition = window.scrollY;
        const insideSelectedWork = selectedWorkSection
            && currentPosition >= selectedWorkSection.offsetTop - 1
            && currentPosition < selectedWorkSection.offsetTop + selectedWorkSection.offsetHeight;
        if (currentPosition <= 24) {
            setHeaderHidden(false);
            lastNavPosition = currentPosition;
        } else if (!document.body.classList.contains('menu-open')) {
            if (currentPosition > lastNavPosition + 8) {
                setHeaderHidden(true);
                lastNavPosition = currentPosition;
            } else if (currentPosition < lastNavPosition - 8) {
                setHeaderHidden(Boolean(insideSelectedWork));
                lastNavPosition = currentPosition;
            }
        }
        ticking = false;
    }
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(updateProgress);
            ticking = true;
        }
    }, { passive: true });
    siteHeader?.addEventListener('focusin', () => setHeaderHidden(false));
    updateProgress();

    const slides = Array.from(document.querySelectorAll('.testimonial-slide'));
    const currentLabel = document.querySelector('#slide-current');
    const prev = document.querySelector('#testimonial-prev');
    const next = document.querySelector('#testimonial-next');
    const track = document.querySelector('#testimonial-track');
    let current = 0;
    let touchStart = 0;

    function showSlide(nextIndex) {
        if (!slides.length) return;
        current = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, index) => {
            const active = index === current;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', String(!active));
        });
        if (currentLabel) currentLabel.textContent = String(current + 1).padStart(2, '0');
    }

    prev?.addEventListener('click', () => showSlide(current - 1));
    next?.addEventListener('click', () => showSlide(current + 1));
    track?.addEventListener('touchstart', (event) => { touchStart = event.changedTouches[0].clientX; }, { passive: true });
    track?.addEventListener('touchend', (event) => {
        const distance = event.changedTouches[0].clientX - touchStart;
        if (Math.abs(distance) > 50) showSlide(current + (distance < 0 ? 1 : -1));
    }, { passive: true });

    document.querySelectorAll('[data-project-gallery]').forEach((gallery) => {
        const galleryFrame = gallery.closest('.work-panel-image');
        const gallerySlides = Array.from(gallery.querySelectorAll('.project-gallery-slide'));
        const galleryPrev = galleryFrame?.querySelector('[data-gallery-prev]');
        const galleryNext = galleryFrame?.querySelector('[data-gallery-next]');
        const galleryCurrentLabel = galleryFrame?.querySelector('[data-gallery-current]');
        const galleryFullscreen = galleryFrame?.querySelector('[data-gallery-fullscreen]');
        let galleryCurrent = 0;
        let galleryTouchStart = 0;

        function getFullscreenElement() {
            return document.fullscreenElement || document.webkitFullscreenElement || null;
        }

        function syncGalleryFullscreenButton() {
            if (!galleryFullscreen || !galleryFrame) return;
            const isFullscreen = getFullscreenElement() === galleryFrame;
            galleryFullscreen.setAttribute('aria-pressed', String(isFullscreen));
            galleryFullscreen.setAttribute('aria-label', isFullscreen ? 'Exit full screen' : 'View gallery full screen');
        }

        async function toggleGalleryFullscreen() {
            if (!galleryFrame) return;
            try {
                if (getFullscreenElement() === galleryFrame) {
                    if (document.exitFullscreen) await document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                    return;
                }

                if (getFullscreenElement()) {
                    if (document.exitFullscreen) await document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                }

                if (galleryFrame.requestFullscreen) await galleryFrame.requestFullscreen();
                else if (galleryFrame.webkitRequestFullscreen) galleryFrame.webkitRequestFullscreen();
            } catch (error) {
                // Browsers can reject fullscreen when device policy blocks it.
                syncGalleryFullscreenButton();
            }
        }

        function showProjectSlide(nextIndex) {
            if (!gallerySlides.length) return;
            galleryCurrent = (nextIndex + gallerySlides.length) % gallerySlides.length;
            gallerySlides.forEach((slide, index) => {
                const active = index === galleryCurrent;
                slide.classList.toggle('is-active', active);
                slide.classList.toggle('is-before', index < galleryCurrent);
                slide.setAttribute('aria-hidden', String(!active));
            });
            if (galleryCurrentLabel) galleryCurrentLabel.textContent = String(galleryCurrent + 1).padStart(2, '0');
        }

        galleryPrev?.addEventListener('click', () => showProjectSlide(galleryCurrent - 1));
        galleryNext?.addEventListener('click', () => showProjectSlide(galleryCurrent + 1));
        if (galleryFullscreen && galleryFrame && !galleryFrame.requestFullscreen && !galleryFrame.webkitRequestFullscreen) {
            galleryFullscreen.hidden = true;
        } else {
            galleryFullscreen?.addEventListener('click', toggleGalleryFullscreen);
            document.addEventListener('fullscreenchange', syncGalleryFullscreenButton);
            document.addEventListener('webkitfullscreenchange', syncGalleryFullscreenButton);
        }
        gallery.addEventListener('touchstart', (event) => { galleryTouchStart = event.changedTouches[0].clientX; }, { passive: true });
        gallery.addEventListener('touchend', (event) => {
            const distance = event.changedTouches[0].clientX - galleryTouchStart;
            if (Math.abs(distance) > 45) showProjectSlide(galleryCurrent + (distance < 0 ? 1 : -1));
        }, { passive: true });
    });

    const form = document.querySelector('#inquiry-form');
    form?.addEventListener('submit', (event) => {
        form.querySelectorAll('.field-error').forEach((error) => error.remove());
        form.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));

        const checks = [
            ['name', (value) => value.trim().length >= 2, 'Please enter your name.'],
            ['email', (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value), 'Please enter a valid email address.'],
            ['service', (value) => value !== '', 'Please choose a service.'],
            ['message', (value) => value.trim().length >= 20, 'Please describe your project in at least 20 characters.'],
        ];
        let firstInvalid = null;
        checks.forEach(([name, isValid, message]) => {
            const field = form.elements[name];
            if (!field || isValid(field.value)) return;
            event.preventDefault();
            field.setAttribute('aria-invalid', 'true');
            const error = document.createElement('span');
            error.className = 'field-error';
            error.textContent = message;
            field.insertAdjacentElement('afterend', error);
            firstInvalid = firstInvalid || field;
        });
        firstInvalid?.focus();
    });

    const formMessage = document.querySelector('#form-message');
    if (formMessage) {
        window.setTimeout(() => formMessage.focus(), 100);
    }

    const workSection = document.querySelector('.work-section');
    const workSticky = document.querySelector('.work-sticky');
    const workStage = document.querySelector('[data-work-slider]');
    const workTrack = document.querySelector('#work-track');
    const workPanels = Array.from(document.querySelectorAll('.work-panel'));
    const workCurrent = document.querySelector('#work-current');
    const workNavCurrent = document.querySelector('#work-nav-current');
    const workProgress = document.querySelector('#work-progress-fill');
    const workPrev = document.querySelector('#work-prev');
    const workNext = document.querySelector('#work-next');
    let workIndex = 0;
    let workTransitioning = false;
    let workDesiredIndex = 0;
    let workScrollFrame = 0;

    const workEase = 'power3.inOut';

    function getWorkDepth(panelIndex, activeIndex = workIndex) {
        return (panelIndex - activeIndex + workPanels.length) % workPanels.length;
    }

    function getWorkStackPosition(depth) {
        if (depth === 0) return { y: 0, scale: 1, opacity: 1, zIndex: 3 };
        if (depth === 1) return { y: 13, scale: .97, opacity: .96, zIndex: 2 };
        if (depth === 2) return { y: 26, scale: .94, opacity: .84, zIndex: 1 };
        return { y: 34, scale: .92, opacity: 0, zIndex: 0 };
    }

    function setWorkPanelTransform(panel, position) {
        if (window.gsap) {
            gsap.set(panel, {
                x: 0,
                y: position.y,
                yPercent: 0,
                scale: position.scale,
                rotationX: 0,
                rotationZ: 0,
                opacity: position.opacity,
                zIndex: position.zIndex,
                force3D: true,
                transformOrigin: '50% 0%',
            });
            return;
        }
        panel.style.zIndex = String(position.zIndex);
        panel.style.opacity = String(position.opacity);
        panel.style.transform = `translate3d(0, ${position.y}px, 0) scale(${position.scale})`;
    }

    function syncWorkStack(activeIndex = workIndex) {
        workPanels.forEach((panel, panelIndex) => {
            const depth = getWorkDepth(panelIndex, activeIndex);
            const isActive = depth === 0;
            panel.classList.toggle('is-current', isActive);
            panel.classList.toggle('is-stack-next', depth === 1);
            panel.classList.toggle('is-stack-back', depth === 2);
            panel.setAttribute('aria-hidden', String(!isActive));
            panel.querySelectorAll('a, button, input, select, textarea, [tabindex]').forEach((control) => {
                if (!isActive) {
                    if (!control.hasAttribute('data-work-tabindex')) {
                        control.setAttribute('data-work-tabindex', control.getAttribute('tabindex') ?? '');
                    }
                    control.setAttribute('tabindex', '-1');
                } else if (control.hasAttribute('data-work-tabindex')) {
                    const previousTabIndex = control.getAttribute('data-work-tabindex');
                    if (previousTabIndex === '') control.removeAttribute('tabindex');
                    else control.setAttribute('tabindex', previousTabIndex);
                    control.removeAttribute('data-work-tabindex');
                }
            });
            setWorkPanelTransform(panel, getWorkStackPosition(depth));
        });
    }

    function updateWorkUI() {
        const value = String(workIndex + 1).padStart(2, '0');
        if (workCurrent) workCurrent.textContent = value;
        if (workNavCurrent) workNavCurrent.textContent = value;
        if (workProgress) workProgress.style.transform = `scaleX(${(workIndex + 1) / workPanels.length})`;
        const title = workPanels[workIndex]?.dataset.projectTitle || 'project';
        workStage?.setAttribute('aria-label', `Showing project ${workIndex + 1} of ${workPanels.length}: ${title}`);
    }

    function activateWorkPanel(nextIndex, wrap = true) {
        if (!workPanels.length) return false;
        const targetIndex = wrap
            ? (nextIndex + workPanels.length) % workPanels.length
            : Math.min(workPanels.length - 1, Math.max(0, nextIndex));
        if (targetIndex === workIndex || workTransitioning) return false;

        const outgoing = workPanels[workIndex];
        const incoming = workPanels[targetIndex];
        const followingIndex = (targetIndex + 1) % workPanels.length;
        const following = workPanels[followingIndex];
        const direction = targetIndex > workIndex ? 1 : -1;
        const finishTransition = () => {
            workIndex = targetIndex;
            syncWorkStack();
            updateWorkUI();
            workTransitioning = false;
            if (workDesiredIndex !== workIndex) {
                const nextStep = workIndex + Math.sign(workDesiredIndex - workIndex);
                window.requestAnimationFrame(() => activateWorkPanel(nextStep, false));
            }
        };

        if (!window.gsap) {
            finishTransition();
            return true;
        }

        workTransitioning = true;
        workPanels.forEach((panel) => panel.classList.remove('is-flipping'));
        outgoing.classList.add('is-flipping');

        // A backwards request may target the rear card. Bring it directly beneath
        // the active sheet first so the revealed project is always the requested one.
        gsap.set(incoming, {
            y: 13,
            yPercent: 0,
            scale: .97,
            rotationX: 0,
            rotationZ: 0,
            opacity: .96,
            zIndex: 2,
            force3D: true,
            transformOrigin: '50% 0%',
        });
        if (following !== outgoing && following !== incoming) {
            gsap.set(following, { y: 26, scale: .94, opacity: .84, zIndex: 1, force3D: true });
        }

        const timeline = gsap.timeline({
            defaults: { duration: reducedMotion ? .22 : .82, ease: workEase, overwrite: 'auto' },
            onComplete: () => {
                outgoing.classList.remove('is-flipping');
                finishTransition();
            },
        });

        if (reducedMotion) {
            timeline
                .to(outgoing, { opacity: 0, duration: .16, ease: 'power1.out' }, 0)
                .to(incoming, { y: 0, scale: 1, opacity: 1, duration: .22, ease: 'power1.out' }, .08);
            return true;
        }

        timeline
            .to(outgoing, {
                yPercent: -120,
                rotationX: 11,
                rotationZ: 2.4 * direction,
                opacity: 0,
                transformOrigin: '50% 0%',
                transformPerspective: 1400,
                force3D: true,
            }, 0)
            .to(incoming, {
                y: 0,
                scale: 1,
                opacity: 1,
                force3D: true,
            }, 0);

        if (following !== outgoing && following !== incoming) {
            timeline.to(following, {
                y: 13,
                scale: .97,
                opacity: .96,
                force3D: true,
            }, 0);
        }
        return true;
    }

    function scrollToWorkSlot(targetIndex) {
        if (!workSection || workPanels.length < 2) return;
        const pinDistance = Math.max(0, workSection.offsetHeight - window.innerHeight);
        const scrollSlots = workPanels.length;
        const slot = (targetIndex + .5) / scrollSlots;
        window.scrollTo({
            top: workSection.offsetTop + pinDistance * slot,
            behavior: 'auto',
        });
    }

    function navigateWork(nextIndex, wrap = false, syncScroll = true) {
        const targetIndex = wrap
            ? (nextIndex + workPanels.length) % workPanels.length
            : Math.min(workPanels.length - 1, Math.max(0, nextIndex));
        const desiredChanged = targetIndex !== workDesiredIndex;
        workDesiredIndex = targetIndex;
        const changed = activateWorkPanel(targetIndex, false);
        if (syncScroll && (changed || desiredChanged)) scrollToWorkSlot(targetIndex);
        return changed || desiredChanged;
    }

    function configurePinnedWork() {
        if (!workSection || !workSticky || !workPanels.length) return;
        // One viewport of pinned scrolling per project, plus the visible stage.
        workSection.style.height = `${Math.max(3, workPanels.length + 1) * 100}svh`;
        queueWorkScrollSync();
    }

    function syncWorkToScroll() {
        workScrollFrame = 0;
        if (!workSection || workPanels.length < 2) return;
        const pinDistance = Math.max(1, workSection.offsetHeight - window.innerHeight);
        const progress = Math.min(1, Math.max(0, (window.scrollY - workSection.offsetTop) / pinDistance));
        const scrollPosition = progress * workPanels.length;
        workDesiredIndex = Math.min(workPanels.length - 1, Math.floor(scrollPosition));
        if (!workTransitioning && workDesiredIndex !== workIndex) {
            const nextStep = workIndex + Math.sign(workDesiredIndex - workIndex);
            activateWorkPanel(nextStep, false);
        }
    }

    function queueWorkScrollSync() {
        if (workScrollFrame) return;
        workScrollFrame = window.requestAnimationFrame(syncWorkToScroll);
    }

    workSection?.classList.add('work-scroll-enabled');
    workPrev?.addEventListener('click', () => navigateWork(workDesiredIndex - 1, true, true));
    workNext?.addEventListener('click', () => navigateWork(workDesiredIndex + 1, true, true));
    workTrack?.addEventListener('click', (event) => {
        const panel = event.target.closest('.work-panel');
        if (panel && !panel.classList.contains('is-current')) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);
    window.addEventListener('scroll', queueWorkScrollSync, { passive: true });
    configurePinnedWork();
    window.addEventListener('resize', configurePinnedWork, { passive: true });
    syncWorkStack();
    updateWorkUI();
    queueWorkScrollSync();

    const heroSection = document.querySelector('.hero-scroll-section');
    const heroStage = document.querySelector('.hero-stage');
    const heroOpening = document.querySelector('.hero-opening');
    const heroTrack = document.querySelector('.hero-opening-track');
    const heroMutedText = document.querySelector('.hero-headline-muted');
    const heroStar = document.querySelector('.hero-star');
    const heroKicker = document.querySelector('.hero-kicker');
    const heroSupport = document.querySelector('.hero-support');
    const heroFinal = document.querySelector('.hero-final');
    const heroCta = document.querySelector('.hero-cta');
    const heroScrollCue = document.querySelector('.hero-scroll-cue');
    const heroCards = Array.from(document.querySelectorAll('.hero-project-card'));
    const heroAnchors = Array.from(document.querySelectorAll('[data-hero-anchor]'));
    let heroTimeline = null;
    let heroResizeFrame = 0;

    function getHeroAnchorPosition(index) {
        const stageRect = heroStage.getBoundingClientRect();
        const anchorRect = heroAnchors[index].getBoundingClientRect();
        const card = heroCards[index];
        return {
            x: anchorRect.left - stageRect.left + (anchorRect.width - card.offsetWidth) / 2,
            y: anchorRect.top - stageRect.top + (anchorRect.height - card.offsetHeight) / 2,
        };
    }

    function syncHeroCardsToAnchors() {
        heroCards.forEach((card, index) => {
            const destination = getHeroAnchorPosition(index);
            gsap.set(card, {
                x: destination.x,
                y: destination.y,
                scale: 1,
                rotation: 0,
            });
        });
    }

    function getHeroFloatPosition(index, mobile) {
        const width = heroStage.clientWidth;
        const height = heroStage.clientHeight;
        const cardWidth = heroCards[index].offsetWidth;
        const cardHeight = heroCards[index].offsetHeight;
        const edgeGutter = mobile ? 38 : Math.max(34, width * .035);
        const desktopPositions = [
            { x: width * .2 - cardWidth / 2, y: height * .27 - cardHeight / 2 },
            { x: width * .49 - cardWidth / 2, y: height * .47 - cardHeight / 2 },
            { x: width - cardWidth - edgeGutter, y: height * .73 - cardHeight / 2 },
        ];
        const mobilePositions = [
            { x: width * .2 - cardWidth / 2, y: height * .3 - cardHeight / 2 },
            { x: width * .5 - cardWidth / 2, y: height * .47 - cardHeight / 2 },
            { x: width - cardWidth - edgeGutter, y: height * .67 - cardHeight / 2 },
        ];
        return (mobile ? mobilePositions : desktopPositions)[index];
    }

    function positionStaticHeroCards() {
        if (!heroStage || heroCards.length !== heroAnchors.length) return;
        heroCards.forEach((card, index) => {
            const destination = getHeroAnchorPosition(index);
            card.style.transform = `translate3d(${destination.x}px, ${destination.y}px, 0)`;
            card.style.opacity = '1';
            card.style.willChange = 'auto';
        });
    }

    // Reduced-motion fallback: immediately show the completed composition.
    function showStaticHero() {
        if (!heroSection) return;
        heroSection.classList.add('hero-reduced');
        window.requestAnimationFrame(positionStaticHeroCards);
    }

    if (reducedMotion || !window.gsap || !window.ScrollTrigger) {
        showStaticHero();
        window.addEventListener('resize', positionStaticHeroCards);
        document.querySelectorAll('.reveal-text, .project-item').forEach((element) => { element.style.opacity = '1'; });
        return;
    }

    gsap.registerPlugin(ScrollTrigger);

    const workRevealPanel = workPanels[0];
    if (workSection && workRevealPanel) {
        const workRevealTimeline = gsap.timeline({
            scrollTrigger: {
                trigger: workSection,
                start: 'top 78%',
                once: true,
            },
        });
        workRevealTimeline
            .fromTo('.work-section-label', { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: .35, ease: 'power2.out' })
            .fromTo('.work-heading-mask > span', { yPercent: 108 }, { yPercent: 0, duration: .65, stagger: .08, ease: 'power3.out' }, .08)
            .fromTo(workRevealPanel.querySelector('.work-panel-image'), { opacity: 0, scale: .96 }, { opacity: 1, scale: 1, duration: .7, ease: 'power3.out' }, .24)
            .fromTo(workRevealPanel.querySelector('.work-panel-copy'), { opacity: 0, x: 36 }, { opacity: 1, x: 0, duration: .65, ease: 'power3.out' }, .34);

        const workRevealQuote = workRevealPanel.querySelector('.work-panel-quote');
        if (workRevealQuote) {
            workRevealTimeline.fromTo(workRevealQuote, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: .45, ease: 'power2.out' }, .68);
        }
    }

    function createHeroTimeline(mobile) {
        if (!heroSection || !heroStage || heroCards.length !== 3 || heroAnchors.length !== 3) return undefined;

        // Kill the old pinned hero before rebuilding it at another breakpoint.
        ScrollTrigger.getById('nivra-hero')?.kill();
        heroTimeline?.kill();
        heroSection.classList.remove('hero-reduced');

        const rotations = mobile ? [-4, 3, -3] : [-8, 6, -5];
        const heroDarkState = mobile
            ? { backgroundColor: '#052742', boxShadow: '32px 0 0 #052742', duration: 20 }
            : { backgroundColor: '#052742', duration: 20 };
        gsap.set(heroStage, { clearProps: 'backgroundColor,color' });
        gsap.set([heroOpening, heroKicker, heroSupport, heroScrollCue], { clearProps: 'all' });
        gsap.set(heroFinal, { autoAlpha: 0, scale: mobile ? .96 : .9, color: '#052742' });
        gsap.set(heroCta, { autoAlpha: 0, y: 16 });
        gsap.set(heroCards, { x: 0, y: 0, opacity: 0, scale: .25, rotation: 0, willChange: 'transform, opacity' });

        // Pinned hero setup: one master timeline owns the complete sequence.
        heroTimeline = gsap.timeline({
            defaults: { ease: 'none' },
            scrollTrigger: {
                id: 'nivra-hero',
                trigger: heroSection,
                start: 'top top',
                end: () => `+=${heroStage.offsetHeight * (mobile ? 1.8 : 2.5)}`,
                pin: heroStage,
                pinSpacing: false,
                scrub: 1,
                anticipatePin: 1,
                invalidateOnRefresh: true,
                onUpdate(self) {
                    // Mobile browser chrome changes the usable viewport while scrolling.
                    // Once the cards reach the final composition, keep them locked to
                    // their live DOM anchors instead of stale pre-resize coordinates.
                    if (mobile && self.progress >= .8) syncHeroCardsToAnchors();
                    heroCards.forEach((card) => { card.style.willChange = self.progress > .985 ? 'auto' : 'transform, opacity'; });
                },
                onRefresh(self) {
                    if (mobile && self.progress >= .8) {
                        window.requestAnimationFrame(syncHeroCardsToAnchors);
                    }
                },
            },
        });

        // 0–20% — headline movement.
        heroTimeline
            .to(heroTrack, { xPercent: mobile ? -1.5 : -5, duration: 20 }, 0)
            .to(heroMutedText, { color: '#052742', duration: 20 }, 0)
            .to(heroStar, { rotation: mobile ? 120 : 180, duration: 20 }, 0)
            .to(heroScrollCue, { opacity: 0, duration: 6 }, 3);

        // Image pop-out sequence (20–50%): cards appear one by one, then briefly float.
        [20, 29, 38].forEach((start, index) => {
            heroTimeline.to(heroCards[index], {
                x: () => getHeroFloatPosition(index, mobile).x,
                y: () => getHeroFloatPosition(index, mobile).y,
                opacity: 1,
                scale: mobile ? 1.32 : 1.14,
                rotation: rotations[index],
                duration: 9,
                ease: 'elastic.out(1, .7)',
            }, start);
        });
        heroTimeline.to(heroCards, {
            y: (index) => getHeroFloatPosition(index, mobile).y + (index === 1 ? -10 : 8),
            scale: mobile ? 1.26 : 1.1,
            duration: 7,
            stagger: .45,
            ease: 'sine.inOut',
        }, 45);

        // Image-to-text transition (50–80%): the same cards move into measured inline anchors.
        heroTimeline
            .to(heroOpening, { opacity: 0, scale: .72, xPercent: -9, duration: 13, ease: 'power2.inOut' }, 52)
            .to([heroKicker, heroSupport], { opacity: 0, y: -12, duration: 10, ease: 'power2.inOut' }, 52)
            .to(heroFinal, { autoAlpha: 1, scale: 1, duration: 14, ease: 'power2.out' }, 64);

        heroCards.forEach((card, index) => {
            heroTimeline.to(card, {
                x: () => getHeroAnchorPosition(index).x,
                y: () => getHeroAnchorPosition(index).y,
                scale: 1,
                rotation: 0,
                duration: 28,
                ease: 'power3.inOut',
            }, 52);
        });

        // Background transition (80–100%): resolve to navy and reveal the final call to action.
        heroTimeline
            .to(heroStage, heroDarkState, 80)
            .to(heroFinal, { color: '#E9EDE2', duration: 17 }, 81)
            .to(heroCta, { autoAlpha: 1, y: 0, duration: 9, ease: 'power2.out' }, 90);

        return () => {
            ScrollTrigger.getById('nivra-hero')?.kill();
            heroTimeline?.kill();
            heroTimeline = null;
        };
    }

    // Mobile animation: preserve the sequence with shorter pinning and smaller movement distances.
    ScrollTrigger.matchMedia({
        '(min-width: 768px)': () => createHeroTimeline(false),
        '(max-width: 767px)': () => createHeroTimeline(true),
    });

    function refreshHeroMeasurements() {
        if (heroResizeFrame) window.cancelAnimationFrame(heroResizeFrame);
        heroResizeFrame = window.requestAnimationFrame(() => {
            ScrollTrigger.refresh();
        });
    }
    window.addEventListener('resize', refreshHeroMeasurements, { passive: true });

    const heroImageReady = heroCards.map((picture) => {
        const image = picture.querySelector('img');
        if (!image || image.complete) return image?.decode?.().catch(() => undefined) || Promise.resolve();
        return new Promise((resolve) => {
            image.addEventListener('load', resolve, { once: true });
            image.addEventListener('error', resolve, { once: true });
        });
    });
    Promise.all(heroImageReady).then(() => ScrollTrigger.refresh());
    document.fonts?.ready.then(() => ScrollTrigger.refresh());

    // About: restrained, reversible reveals in the section's natural page flow.
    const aboutSection = document.querySelector('.about-section');
    const aboutLabel = document.querySelector('.about-label');
    const aboutCopy = document.querySelector('.about-copy');
    const founderBlock = document.querySelector('.founder-block');
    const founderPhoto = document.querySelector('.founder-photo');
    const founderMeta = document.querySelector('.founder-meta');
    const founderMessage = document.querySelector('.founder-message');
    const founderSignature = document.querySelector('.founder-signature');
    const founderConnector = document.querySelector('.founder-connector');
    const aboutPrinciples = document.querySelector('.about-principles');
    const aboutPrincipleItems = Array.from(document.querySelectorAll('.about-principles li'));
    const aboutWhy = document.querySelector('.about-why');
    const aboutWhyIntro = document.querySelector('.about-why-intro');
    const aboutWhyStatement = document.querySelector('.about-why-statement');
    const aboutWhyPrinciples = Array.from(document.querySelectorAll('.about-why-principle'));

    if (aboutSection && aboutCopy) {
        gsap.timeline({
            scrollTrigger: {
                trigger: aboutCopy,
                start: 'top 86%',
                toggleActions: 'play none none reverse',
            },
        })
            .fromTo(aboutLabel, { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: .4, ease: 'power2.out' }, 0)
            .fromTo(aboutCopy, { opacity: 0, y: 34 }, { opacity: 1, y: 0, duration: .65, ease: 'power3.out' }, .08);
    }

    if (founderBlock && founderPhoto) {
        gsap.timeline({
            scrollTrigger: {
                trigger: founderBlock,
                start: 'top 86%',
                toggleActions: 'play none none reverse',
            },
        })
            .fromTo(founderBlock, { opacity: 0, y: 24 }, { opacity: 1, y: 0, duration: .55, ease: 'power3.out' }, 0)
            .fromTo(founderPhoto, { opacity: 0, scale: .85 }, { opacity: 1, scale: 1, duration: .5, ease: 'power2.out' }, .05)
            .fromTo(founderMeta, { opacity: 0, x: 12 }, { opacity: 1, x: 0, duration: .42, ease: 'power2.out' }, .12)
            .fromTo(founderMessage, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: .5, ease: 'power2.out' }, .24)
            .fromTo([founderSignature, founderConnector], { opacity: 0, y: 8 }, { opacity: 1, y: 0, duration: .4, stagger: .06, ease: 'power2.out' }, .34)
            .fromTo(founderConnector.querySelector('i'), { scaleX: 0 }, { scaleX: 1, duration: .45, ease: 'power2.inOut' }, .38);
    }

    if (aboutPrinciples) {
        gsap.timeline({
            scrollTrigger: {
                trigger: aboutPrinciples,
                start: 'top 86%',
                toggleActions: 'play none none reverse',
            },
        })
            .fromTo(aboutPrinciples, {
                opacity: 0,
                y: 28,
            }, {
                opacity: 1,
                y: 0,
                duration: .58,
                ease: 'power3.out',
            }, 0)
            .fromTo(aboutPrincipleItems, {
                opacity: 0,
                y: 12,
            }, {
                opacity: 1,
                y: 0,
                duration: .38,
                stagger: .06,
                ease: 'power2.out',
            }, .16);
    }

    if (aboutWhy && aboutWhyIntro && aboutWhyStatement) {
        gsap.timeline({
            scrollTrigger: {
                trigger: aboutWhy,
                start: 'top 84%',
                toggleActions: 'play none none reverse',
            },
        })
            .fromTo(aboutWhyIntro, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: .45, ease: 'power2.out' }, 0)
            .fromTo(aboutWhyStatement, { opacity: 0, y: 34 }, { opacity: 1, y: 0, duration: .65, ease: 'power3.out' }, .06)
            .fromTo(aboutWhyPrinciples, { opacity: 0, y: 24 }, {
                opacity: 1,
                y: 0,
                duration: .5,
                stagger: .08,
                ease: 'power2.out',
            }, .22);
    }

    // Services stay in the normal page flow and receive a simple one-time reveal.
    gsap.utils.toArray('.service-card').forEach((card) => {
        const cardContent = Array.from(card.children);
        const serviceReveal = gsap.timeline({
            scrollTrigger: {
                trigger: card,
                start: 'top 88%',
                once: true,
            },
        });

        serviceReveal
            .fromTo(card, {
                opacity: 0,
                y: 32,
            }, {
                opacity: 1,
                y: 0,
                duration: .65,
                ease: 'power3.out',
            })
            .fromTo(card, {
                '--service-line-scale': 0,
            }, {
                '--service-line-scale': 1,
                duration: .45,
                ease: 'power2.out',
            }, .08)
            .fromTo(cardContent, {
                opacity: 0,
                y: 12,
            }, {
                opacity: 1,
                y: 0,
                duration: .42,
                stagger: .035,
                ease: 'power2.out',
            }, .14);
    });

    const socialSection = document.querySelector('#social');
    const socialIntro = socialSection?.querySelector('.social-intro');
    const socialLinks = socialSection ? Array.from(socialSection.querySelectorAll('.social-link')) : [];
    if (socialSection && socialIntro && socialLinks.length) {
        gsap.timeline({
            scrollTrigger: {
                trigger: socialSection,
                start: 'top 72%',
                once: true,
            },
        })
            .fromTo(Array.from(socialIntro.children), { opacity: 0, y: 30 }, {
                opacity: 1,
                y: 0,
                duration: .7,
                stagger: .08,
                ease: 'power3.out',
            })
            .fromTo(socialLinks, { opacity: 0, y: 30 }, {
                opacity: 1,
                y: 0,
                duration: .8,
                stagger: .12,
                ease: 'power3.out',
            }, .18);
    }

    const contactSection = document.querySelector('#contact');
    const contactLabel = contactSection?.querySelector('.eyebrow');
    const contactTitle = contactSection?.querySelector('.contact-title');
    const contactTitleLines = contactTitle ? Array.from(contactTitle.querySelectorAll('.contact-title-line > span, .contact-title-line > em')) : [];
    const contactAccent = contactTitle?.querySelector('.contact-title-accent');
    const contactUnderline = contactAccent?.querySelector('i');

    if (contactSection && contactTitle && contactTitleLines.length) {
        const contactTimeline = gsap.timeline({
            scrollTrigger: {
                trigger: contactSection,
                start: 'top 76%',
                once: true,
            },
        });

        contactTimeline
            .fromTo(contactLabel, { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: .4, ease: 'power2.out' }, 0)
            .fromTo(contactTitleLines, {
                yPercent: 115,
                rotation: 2,
            }, {
                yPercent: 0,
                rotation: 0,
                duration: 1.05,
                stagger: .13,
                ease: 'power4.out',
            }, .08)
            .fromTo(contactAccent, { xPercent: -4 }, { xPercent: 0, duration: 1.15, ease: 'power3.out' }, .18)
            .fromTo(contactUnderline, { scaleX: 0 }, { scaleX: 1, duration: .9, ease: 'power3.inOut' }, .56);
    }

    gsap.utils.toArray('.reveal-text').forEach((text) => {
        gsap.fromTo(text, { opacity: 0, y: 45 }, {
            opacity: 1, y: 0, duration: .9, ease: 'power3.out',
            scrollTrigger: { trigger: text, start: 'top 85%', once: true },
        });
    });
    gsap.utils.toArray('.project-item').forEach((project) => {
        gsap.fromTo(project, { opacity: 0, y: 65 }, {
            opacity: 1, y: 0, duration: 1, ease: 'power3.out',
            scrollTrigger: { trigger: project, start: 'top 82%', once: true },
        });
    });
})();
