<script>
    (() => {
        const root = document.querySelector('[data-order-boards]');
        const notice = root.querySelector('[data-board-notice]');
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
        let dragging, picked, touch, target, scrollFrame;
        let suppressClickUntil = 0;
        let saves = Promise.resolve();

        const boardOf = element => element.closest('.board-shell').querySelector('[data-milestone-board]');
        const lanesOf = board => [...board.querySelectorAll('[data-lane]')];
        const gripOf = card => card.querySelector('[data-task-grip]');

        function announce(message) {
            notice.hidden = false;
            notice.textContent = message;
        }

        function selectVisibleLane(board) {
            const left = board.getBoundingClientRect().left;
            const lane = lanesOf(board).reduce((closest, next) =>
                Math.abs(next.getBoundingClientRect().left - left) < Math.abs(closest.getBoundingClientRect().left - left) ? next : closest);
            board.closest('.board-shell').querySelectorAll('[data-lane-tab]').forEach(tab => {
                if (tab.dataset.laneTab === lane.dataset.lane) tab.setAttribute('aria-current', 'true');
                else tab.removeAttribute('aria-current');
            });
        }

        function showLane(lane, animate = true) {
            const board = boardOf(lane);
            if (board.scrollWidth <= board.clientWidth) return;
            board.scrollBy({
                left: lane.getBoundingClientRect().left - board.getBoundingClientRect().left - 2,
                behavior: animate && !reducedMotion.matches ? 'smooth' : 'instant',
            });
            if (!animate) selectVisibleLane(board);
        }

        function dropTarget(element, card) {
            const hit = element?.closest('[data-lane], [data-lane-tab]');
            if (!hit || boardOf(hit) !== boardOf(card)) return null;
            return hit;
        }

        function laneFor(hit) {
            return hit.dataset.laneTab ? document.getElementById(hit.getAttribute('aria-controls')) : hit;
        }

        function highlight(hit) {
            if (target === hit) return;
            target?.classList.remove('is-over');
            target = hit;
            target?.classList.add('is-over');
        }

        function pick(card) {
            picked?.classList.remove('is-picked');
            if (picked) gripOf(picked).setAttribute('aria-pressed', 'false');
            picked = card;
            picked?.classList.add('is-picked');
            if (picked) gripOf(picked).setAttribute('aria-pressed', 'true');
        }

        function move(card, lane, restoreFocus = false) {
            if (!card.dataset.updateUrl || card.hasAttribute('aria-busy') || lane === card.closest('[data-lane]')) return;
            card.setAttribute('aria-busy', 'true');
            saves = saves.then(async () => {
                try {
                    const response = await fetch(card.dataset.updateUrl, {
                        method: 'PATCH',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ status: lane.dataset.lane }),
                    });
                    if (!response.ok) throw new Error();
                    const progress = await response.json();
                    const source = card.closest('[data-lane]');
                    lane.querySelector('.lane-content').append(card);
                    for (const column of [source, lane]) {
                        column.querySelector('[data-lane-count]').textContent = column.querySelectorAll('[data-task]').length;
                    }
                    boardOf(lane).closest('.milestone').querySelector('[data-done-count]').textContent =
                        boardOf(lane).querySelectorAll('[data-lane="done"] [data-task]').length;
                    root.querySelector('[data-delivery-arc]').setAttribute('stroke-dasharray', progress.percent + ' 100');
                    root.querySelector('[data-delivery-percent]').textContent = progress.percent + '%';
                    root.querySelector('[data-delivery-dial]').setAttribute('aria-label', @js(__('consultancy_engagement.task_board.progress', ['percent' => '__PERCENT__'])).replace('__PERCENT__', progress.percent));
                    showLane(lane, false);
                    if (restoreFocus) gripOf(card).focus({ preventScroll: true });
                    announce(@js(__('consultancy_engagement.task_board.updated')));
                } catch {
                    announce(@js(__('consultancy_engagement.task_board.failed')));
                } finally {
                    card.removeAttribute('aria-busy');
                }
            });
        }

        root.querySelectorAll('[data-milestone-board]').forEach(board => {
            board.addEventListener('scroll', () => selectVisibleLane(board), { passive: true });
        });

        root.addEventListener('click', event => {
            const grip = event.target.closest('[data-task-grip]');
            const tab = event.target.closest('[data-lane-tab]');
            if (grip) {
                if (performance.now() < suppressClickUntil) return;
                const card = grip.closest('[data-task]');
                if (card.hasAttribute('aria-busy')) return;
                pick(picked === card ? null : card);
                if (picked) announce(@js(__('consultancy_engagement.task_board.picked')));
                return;
            }
            const hit = picked && dropTarget(event.target, picked);
            if (hit) {
                const card = picked;
                pick(null);
                move(card, laneFor(hit));
            }
            if (tab) showLane(laneFor(tab));
        });

        root.addEventListener('keydown', event => {
            if (event.key === 'Escape') pick(null);
            const grip = event.target.closest('[data-task-grip]');
            if (!grip || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            const card = grip.closest('[data-task]');
            const lanes = lanesOf(boardOf(card));
            const destination = lanes[lanes.indexOf(card.closest('[data-lane]')) + (event.key === 'ArrowRight' ? 1 : -1)];
            event.preventDefault();
            if (!destination) return;
            pick(null);
            move(card, destination, true);
        });

        root.addEventListener('dragstart', event => {
            const card = event.target.closest('[data-task][draggable="true"]');
            if (!card || touch || card.hasAttribute('aria-busy')) {
                event.preventDefault();
                return;
            }
            pick(null);
            dragging = card;
            card.classList.add('is-dragging');
            event.dataTransfer.setData('text/plain', card.querySelector('h5').textContent);
            event.dataTransfer.effectAllowed = 'move';
        });

        root.addEventListener('dragover', event => {
            if (!dragging) return;
            const hit = dropTarget(event.target, dragging);
            highlight(hit);
            if (!hit) return;
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        });

        root.addEventListener('drop', event => {
            if (!dragging) return;
            const hit = dropTarget(event.target, dragging);
            if (!hit) return;
            event.preventDefault();
            move(dragging, laneFor(hit));
        });

        root.addEventListener('dragend', () => {
            dragging?.classList.remove('is-dragging');
            dragging = null;
            highlight(null);
        });

        function scrollNearEdge() {
            const bounds = touch.board.getBoundingClientRect();
            const { x, y } = touch;
            if (y >= bounds.top && y <= bounds.bottom && x >= bounds.left && x <= bounds.right) {
                if (x > bounds.right - 32) touch.board.scrollLeft += 8;
                else if (x < bounds.left + 32) touch.board.scrollLeft -= 8;
                highlight(dropTarget(document.elementFromPoint(x, y), touch.card));
            }
            scrollFrame = requestAnimationFrame(scrollNearEdge);
        }

        root.addEventListener('pointerdown', event => {
            const grip = event.target.closest('[data-task-grip]');
            if (!grip || event.pointerType === 'mouse' || !event.isPrimary || touch) return;
            const card = grip.closest('[data-task]');
            if (card.hasAttribute('aria-busy')) return;
            grip.setPointerCapture(event.pointerId);
            touch = { id: event.pointerId, card, board: boardOf(card), startX: event.clientX, startY: event.clientY };
        });

        root.addEventListener('pointermove', event => {
            if (!touch || touch.id !== event.pointerId) return;
            const x = touch.x = event.clientX;
            const y = touch.y = event.clientY;
            if (!touch.ghost) {
                if (Math.hypot(x - touch.startX, y - touch.startY) < 7) return;
                pick(null);
                touch.ghost = document.createElement('span');
                touch.ghost.className = 'task-ghost';
                touch.ghost.setAttribute('aria-hidden', 'true');
                touch.ghost.textContent = touch.card.querySelector('h5').textContent;
                root.append(touch.ghost);
                touch.card.classList.add('is-dragging');
                touch.board.classList.add('is-touch-drag');
                scrollFrame = requestAnimationFrame(scrollNearEdge);
            }
            touch.ghost.style.left = Math.max(4, Math.min(x - 22, window.innerWidth - touch.ghost.offsetWidth - 4)) + 'px';
            touch.ghost.style.top = Math.max(4, y - touch.ghost.offsetHeight - 10) + 'px';
            highlight(dropTarget(document.elementFromPoint(x, y), touch.card));
        });

        function endTouch(event) {
            if (!touch || touch.id !== event.pointerId) return;
            if (touch.ghost) {
                cancelAnimationFrame(scrollFrame);
                suppressClickUntil = performance.now() + 350;
                const hit = event.type === 'pointerup' && dropTarget(document.elementFromPoint(event.clientX, event.clientY), touch.card);
                touch.ghost.remove();
                touch.card.classList.remove('is-dragging');
                touch.board.classList.remove('is-touch-drag');
                if (hit) move(touch.card, laneFor(hit));
                highlight(null);
            }
            touch = null;
        }

        root.addEventListener('pointerup', endTouch);
        root.addEventListener('pointercancel', endTouch);
        root.addEventListener('lostpointercapture', endTouch);
    })();
</script>
