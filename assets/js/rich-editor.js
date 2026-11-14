
(function () {
    'use strict';

    var WARNA = [
        ['Kuning', '#fff176'], ['Hijau', '#b9f6ca'], ['Biru', '#b3e5fc'],
        ['Merah muda', '#f8bbd0'], ['Oranye', '#ffe0b2'], ['Tanpa highlight', 'transparent']
    ];
    var GAYA_UL = [['Bulat', 'disc'], ['Lingkaran', 'circle'], ['Kotak', 'square']];
    var GAYA_OL = [['1, 2, 3', 'decimal'], ['a, b, c', 'lower-alpha'], ['i, ii, iii', 'lower-roman']];

    var IKON = {
        highlight: '<svg viewBox="0 0 24 24" width="16" height="16"><path d="M4 21h16v2.5H4z" fill="#f9c900"/><path d="M14.5 2.5l6 6-9 9H5.5v-6z" fill="currentColor"/></svg>',
        ul: '<svg viewBox="0 0 24 24" width="18" height="18"><circle cx="4" cy="6" r="1.8" fill="currentColor"/><circle cx="4" cy="12" r="1.8" fill="currentColor"/><circle cx="4" cy="18" r="1.8" fill="currentColor"/><path d="M9 6h12M9 12h12M9 18h12" stroke="currentColor" stroke-width="2"/></svg>',
        ol: '<svg viewBox="0 0 24 24" width="18" height="18"><text x="1" y="8" font-size="7" fill="currentColor">1</text><text x="1" y="14" font-size="7" fill="currentColor">2</text><text x="1" y="20" font-size="7" fill="currentColor">3</text><path d="M9 6h12M9 12h12M9 18h12" stroke="currentColor" stroke-width="2"/></svg>',
        caret: '<svg viewBox="0 0 10 6" width="9" height="6"><path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>'
    };

    function el(tag, cls, html) {
        var e = document.createElement(tag);
        if (cls) { e.className = cls; }
        if (html !== undefined) { e.innerHTML = html; }
        return e;
    }

    function buat(ta) {
        ta.setAttribute('data-rich-ready', '1');

        var wrap = el('div', 'rte');
        var bar = el('div', 'rte-bar');
        var area = el('div', 'rte-area');
        var warnaTerakhir = WARNA[0][1];
        var menuTerbuka = null;

        area.contentEditable = 'true';
        area.setAttribute('role', 'textbox');
        area.setAttribute('aria-multiline', 'true');
        area.setAttribute('data-placeholder', ta.getAttribute('placeholder') || 'Tulis di sini...');
        area.style.minHeight = (parseInt(ta.getAttribute('rows') || '3', 10) * 24 + 20) + 'px';
        area.innerHTML = ta.value;

        ta.parentNode.insertBefore(wrap, ta);
        wrap.appendChild(bar);
        wrap.appendChild(area);
        wrap.appendChild(ta);
        ta.style.display = 'none';

        if (ta.id) {
            var label = document.querySelector('label[for="' + ta.id + '"]');
            if (label) {
                label.addEventListener('click', function (e) { e.preventDefault(); area.focus(); });
            }
        }

        function sinkron() {
            var h = area.innerHTML.replace(/\u200B/g, '');
            if (area.textContent.replace(/\u200B/g, '').trim() === '' && !area.querySelector('li')) { h = ''; }
            ta.value = h;
        }
        ta._rteSinkron = sinkron;

        function tutupMenu() {
            if (menuTerbuka) { menuTerbuka.classList.remove('show'); menuTerbuka = null; }
        }

        function tombol(cls, html, judul, aksi) {
            var b = el('button', 'rte-btn ' + cls, html);
            b.type = 'button';
            b.title = judul;
            b.setAttribute('aria-label', judul);
            b.addEventListener('mousedown', function (e) { e.preventDefault(); }); // jaga seleksi teks
            b.addEventListener('click', aksi);
            return b;
        }

        function menu(item, pilih) {
            var m = el('div', 'rte-menu');
            item.forEach(function (it) {
                var o = el('button', 'rte-menu-item', it[2] || it[0]);
                o.type = 'button';
                o.addEventListener('mousedown', function (e) { e.preventDefault(); });
                o.addEventListener('click', function () { tutupMenu(); pilih(it[1]); });
                m.appendChild(o);
            });
            return m;
        }

        function grup(utama, item, pilih) {
            var g = el('span', 'rte-group');
            var m = menu(item, pilih);
            var caret = tombol('rte-caret', IKON.caret, 'Pilihan lainnya', function () {
                var buka = !m.classList.contains('show');
                tutupMenu();
                if (buka) { m.classList.add('show'); menuTerbuka = m; }
            });
            g.appendChild(utama);
            g.appendChild(caret);
            g.appendChild(m);
            return g;
        }

        function perintah(nama, nilai) {
            area.focus();
            document.execCommand(nama, false, nilai === undefined ? null : nilai);
            sinkron();
            perbaruiStatus();
        }

        function sedangHighlight() {
            var sel = window.getSelection();
            if (!sel.rangeCount) { return false; }
            var node = sel.anchorNode;
            node = node && node.nodeType === 3 ? node.parentNode : node;
            var span = node && node.closest ? node.closest('span[style*="background-color"]') : null;
            return !!(span && area.contains(span) && span.style.backgroundColor !== 'transparent');
        }

        // kursor ada di ujung teks ber-highlight: pindahkan keluar, jadi teks berikutnya polos
        function keluarDariHighlight() {
            var sel = window.getSelection();
            if (!sel.rangeCount || !sel.isCollapsed) { return false; }
            var node = sel.anchorNode;
            node = node && node.nodeType === 3 ? node.parentNode : node;
            var span = node && node.closest ? node.closest('span[style*="background-color"]') : null;
            if (!span || !area.contains(span)) { return false; }

            // hanya kalau tidak ada teks tersisa di dalam highlight setelah kursor
            var sisa = document.createRange();
            sisa.setStart(sel.anchorNode, sel.anchorOffset);
            sisa.setEnd(span, span.childNodes.length);
            if (sisa.toString().replace(/\u200B/g, '') !== '') { return false; }

            var kosong = document.createTextNode('\u200B');
            span.parentNode.insertBefore(kosong, span.nextSibling);
            var r = document.createRange();
            r.setStart(kosong, 1);
            r.collapse(true);
            sel.removeAllRanges();
            sel.addRange(r);
            return true;
        }

        function matikanHighlight() {
            area.focus();
            if (!keluarDariHighlight()) { beriHighlight('transparent', true); }
            sinkron();
        }

        function beriHighlight(warna, jangan_ingat) {
            if (!jangan_ingat && warna !== 'transparent') { warnaTerakhir = warna; }
            area.focus();
            document.execCommand('styleWithCSS', false, true); // hanya untuk highlight
            document.execCommand('hiliteColor', false, warna);
            document.execCommand('styleWithCSS', false, false);
            sinkron();
        }

        function gayaDaftar(tag, gaya) {
            area.focus();
            var sel = window.getSelection();
            if (!sel.rangeCount) { return; }
            var node = sel.anchorNode;
            node = node && node.nodeType === 3 ? node.parentNode : node;
            var daftar = node && node.closest ? node.closest(tag) : null;
            if (!daftar || !area.contains(daftar)) {
                document.execCommand(tag === 'ul' ? 'insertUnorderedList' : 'insertOrderedList');
                node = sel.anchorNode;
                node = node && node.nodeType === 3 ? node.parentNode : node;
                daftar = node && node.closest ? node.closest(tag) : null;
            }
            if (daftar && area.contains(daftar)) { daftar.style.listStyleType = gaya; }
            sinkron();
            perbaruiStatus();
        }

        var bBold = tombol('rte-b', 'B', 'Tebal (Ctrl+B)', function () { perintah('bold'); });
        var bItalic = tombol('rte-i', 'I', 'Miring (Ctrl+I)', function () { perintah('italic'); });
        var bUnder = tombol('rte-u', 'U', 'Garis bawah (Ctrl+U)', function () { perintah('underline'); });
        var bHi = tombol('rte-hi', IKON.highlight, 'Highlight', function () {
            // klik lagi pada teks yang sudah ter-highlight = hapus highlight
            if (sedangHighlight()) { matikanHighlight(); } else { beriHighlight(warnaTerakhir); }
        });
        var bUl = tombol('rte-ul', IKON.ul, 'Daftar poin', function () { perintah('insertUnorderedList'); });
        var bOl = tombol('rte-ol', IKON.ol, 'Daftar nomor', function () { perintah('insertOrderedList'); });

        var itemWarna = WARNA.map(function (w) {
            var chip = w[1] === 'transparent'
                ? '<span class="rte-chip rte-chip-none"></span>'
                : '<span class="rte-chip" style="background:' + w[1] + '"></span>';
            return [w[0], w[1], chip + w[0]];
        });
        var itemUl = GAYA_UL.map(function (g) { return [g[0], g[1], g[0]]; });
        var itemOl = GAYA_OL.map(function (g) { return [g[0], g[1], g[0]]; });

        bar.appendChild(bBold);
        bar.appendChild(bItalic);
        bar.appendChild(bUnder);
        bar.appendChild(grup(bHi, itemWarna, function (w) {
            if (w === 'transparent') { matikanHighlight(); } else { beriHighlight(w); }
        }));
        bar.appendChild(el('span', 'rte-sep'));
        bar.appendChild(grup(bUl, itemUl, function (g) { gayaDaftar('ul', g); }));
        bar.appendChild(grup(bOl, itemOl, function (g) { gayaDaftar('ol', g); }));

        function perbaruiStatus() {
            if (document.activeElement !== area) { return; }
            [[bBold, 'bold'], [bItalic, 'italic'], [bUnder, 'underline'],
             [bUl, 'insertUnorderedList'], [bOl, 'insertOrderedList']].forEach(function (p) {
                var aktif = false;
                try { aktif = document.queryCommandState(p[1]); } catch (e) { aktif = false; }
                p[0].classList.toggle('on', aktif);
            });
        }

        area.addEventListener('input', function () {
            if (area.textContent.trim() === '' && !area.querySelector('li') && area.innerHTML !== '') {
                area.innerHTML = ''; // supaya placeholder muncul lagi
            }
            sinkron();
        });
        area.addEventListener('keyup', perbaruiStatus);
        area.addEventListener('mouseup', perbaruiStatus);
        document.addEventListener('selectionchange', perbaruiStatus);

        // tempel sebagai teks biasa, supaya tidak membawa gaya/HTML dari luar
        area.addEventListener('paste', function (e) {
            e.preventDefault();
            var teks = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, teks);
        });

        document.addEventListener('mousedown', function (e) {
            if (menuTerbuka && !menuTerbuka.parentNode.contains(e.target)) { tutupMenu(); }
        });

        if (ta.form) { ta.form.addEventListener('submit', sinkron); }
        sinkron();
    }

    function init(root) {
        (root || document).querySelectorAll('textarea.rich-field:not([data-rich-ready])').forEach(buat);
    }

    // pastikan isi editor masuk ke textarea sebelum form dikirim lewat fetch
    function sinkronForm(form) {
        form.querySelectorAll('textarea[data-rich-ready]').forEach(function (t) {
            if (t._rteSinkron) { t._rteSinkron(); }
        });
    }

    window.RichEditor = { init: init, sinkron: sinkronForm };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }
})();