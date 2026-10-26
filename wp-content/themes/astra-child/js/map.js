(function () {
    'use strict';

    var mapEl = document.getElementById('mn-interactive-map');
    var svgEl = document.getElementById('mn-map-svg');
    var currentButton = document.getElementById('mn-map-current-button');
    if (!mapEl || !svgEl) { return; }

    // 1. Отрисовка извилистой тропинки между центрами кружков
    function drawPath() {
        var nodes = Array.from(mapEl.querySelectorAll('.mn-node'));
        if (nodes.length < 2) { return; }

        var mapRect = mapEl.getBoundingClientRect();
        var contentHeight = mapEl.scrollHeight;
        svgEl.style.setProperty('--mn-map-content-height', contentHeight + 'px');
        svgEl.setAttribute('viewBox', '0 0 ' + mapEl.clientWidth + ' ' + contentHeight);
        svgEl.innerHTML = '';

        for (var i = 0; i < nodes.length - 1; i++) {
            var r1 = nodes[i].getBoundingClientRect();
            var r2 = nodes[i + 1].getBoundingClientRect();

            var x1 = r1.left + r1.width / 2 - mapRect.left;
            var y1 = r1.top + r1.height / 2 - mapRect.top + mapEl.scrollTop;
            var x2 = r2.left + r2.width / 2 - mapRect.left;
            var y2 = r2.top + r2.height / 2 - mapRect.top + mapEl.scrollTop;

            var midY = (y1 + y2) / 2;
            var d = 'M ' + x1 + ' ' + y1 + ' C ' + x1 + ' ' + midY + ', ' + x2 + ' ' + midY + ', ' + x2 + ' ' + y2;

            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', '#7f8c8d');
            path.setAttribute('stroke-width', '5');
            path.setAttribute('stroke-linecap', 'round');

            // Если следующий узел закрыт — делаем линию пунктирной (как на макете!)
            if (nodes[i + 1].dataset.status === 'locked') {
                path.setAttribute('stroke-dasharray', '10, 10');
            }

            svgEl.appendChild(path);
        }
    }

    function scrollToCurrentNode(behavior) {
        var currentId = mapEl.dataset.currentNode;
        var currentNodeEl = mapEl.querySelector('.mn-node[data-node-id="' + currentId + '"]');
        if (currentNodeEl) {
            var rect = currentNodeEl.getBoundingClientRect();
            var mapRect = mapEl.getBoundingClientRect();
            var nodeTop = rect.top - mapRect.top + mapEl.scrollTop;
            var targetTop = nodeTop - (mapEl.clientHeight - currentNodeEl.offsetHeight) / 2;
            mapEl.scrollTo({ top: Math.max(0, targetTop), behavior: behavior || 'auto' });
        }
    }

    // 2. Анимация перепрыгивания кота при возвращении из пройденного урока
    function handleCatJump() {
        var currentId = mapEl.dataset.currentNode;
        var lastCatId = mapEl.dataset.lastCatNode;
        var currentNodeEl = mapEl.querySelector('.mn-node[data-node-id="' + currentId + '"]');
        var catToken = document.getElementById('mn-map-cat-token');

        // Если кот сидел на старом узле, а теперь открылся новый — прыгаем!
        if (catToken && currentNodeEl && currentId && lastCatId && currentId !== lastCatId) {
            var oldNodeEl = mapEl.querySelector('.mn-node[data-node-id="' + lastCatId + '"]');
            if (oldNodeEl) {
                setTimeout(function () {
                    var rOld = oldNodeEl.getBoundingClientRect();
                    var rNew = currentNodeEl.getBoundingClientRect();
                    var dx = (rNew.left + rNew.width / 2) - (rOld.left + rOld.width / 2);
                    var dy = (rNew.top + rNew.height / 2) - (rOld.top + rOld.height / 2);

                    catToken.style.transform = 'translate(calc(-50% + ' + dx + 'px), ' + dy + 'px) scale(1.25)';

                    setTimeout(function () {
                        catToken.style.transform = 'translateX(-50%)';
                        currentNodeEl.appendChild(catToken);
                        mapEl.dataset.lastCatNode = currentId;

                        // Сохраняем новую позицию кота в user_meta на сервере
                        var fd = new FormData();
                        fd.append('action', 'mn_update_cat_node');
                        fd.append('nonce', mapEl.dataset.nonce);
                        fd.append('node_id', currentId);
                        fetch(mapEl.dataset.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                    }, 720);
                }, 500);
            }
        }
    }

    // Автоматический пересчёт тропинки при повороте экрана или ресайзе
    if (window.ResizeObserver) {
        new ResizeObserver(drawPath).observe(mapEl);
    } else {
        window.addEventListener('resize', drawPath);
    }

    window.addEventListener('load', function () {
        drawPath();
        scrollToCurrentNode('auto');
        handleCatJump();
    });

    if (currentButton) {
        currentButton.addEventListener('click', function () {
            scrollToCurrentNode('smooth');
        });
    }
})();
