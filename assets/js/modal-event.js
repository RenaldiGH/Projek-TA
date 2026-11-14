/* Klik link ber-atribut data-modal-event -> form dibuka sebagai kanvas di tengah
   (latar halaman tetap tampil, buram). Tanpa JS, link tetap membuka halamannya biasa. */
(function () {
    'use strict';

    var overlay = document.createElement('div');
    overlay.className = 'me-overlay';
    overlay.innerHTML =
        '<div class="me-card" role="dialog" aria-modal="true" aria-labelledby="meJudul">' +
        '<div class="me-head"><h3 id="meJudul"></h3>' +
        '<button type="button" class="me-close" aria-label="Tutup">&times;</button></div>' +
        '<div class="me-body"></div></div>';
    document.body.appendChild(overlay);

    var judul = overlay.querySelector('#meJudul');
    var isi = overlay.querySelector('.me-body');
    var urlAktif = '';

    function tutup() {
        overlay.classList.remove('show');
        document.body.classList.remove('me-lock');
        isi.innerHTML = '';
    }

    function tampilkan(html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var kartu = doc.querySelector('.form-card');
        if (!kartu) { window.location.href = urlAktif; return; }

        isi.innerHTML = '';
        var prev = kartu.previousElementSibling;
        if (prev && prev.classList.contains('alert')) {
            isi.appendChild(document.importNode(prev, true));
        }
        isi.appendChild(document.importNode(kartu, true));

        var form = isi.querySelector('form');
        form.setAttribute('action', urlAktif);
        window.RichEditor.init(isi);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            window.RichEditor.sinkron(form);
            var kirim = form.querySelector('[type="submit"]');
            if (kirim) { kirim.disabled = true; }

            fetch(urlAktif, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                redirect: 'manual'          // redirect = berhasil; pesan flash jangan sampai terpakai
            }).then(function (res) {
                if (res.type === 'opaqueredirect') { window.location.reload(); return null; }
                return res.text();
            }).then(function (teks) {
                if (teks !== null && teks !== undefined) { tampilkan(teks); }
            }).catch(function () {
                if (kirim) { kirim.disabled = false; }
                alert('Gagal mengirim data, coba lagi.');
            });
        });

        var pertama = form.querySelector('input:not([type="hidden"]):not([type="file"])');
        if (pertama) { pertama.focus(); }
    }

    function buka(url, teksJudul) {
        urlAktif = url;
        judul.textContent = teksJudul || 'Form';
        isi.innerHTML = '<p class="me-loading">Memuat...</p>';
        overlay.classList.add('show');
        document.body.classList.add('me-lock');

        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(tampilkan)
            .catch(function () {
                isi.innerHTML = '<div class="alert alert-error">Gagal memuat form.</div>';
            });
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('[data-modal-event]') : null;
        if (!a || e.ctrlKey || e.metaKey || e.shiftKey) { return; }
        e.preventDefault();
        buka(a.href, a.getAttribute('data-judul') || a.textContent.replace(/^\s*\+\s*/, '').trim());
    });

    overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) { tutup(); } });
    overlay.querySelector('.me-close').addEventListener('click', tutup);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('show')) { tutup(); }
    });

    // tombol "Batal" di dalam form menutup modal, bukan pindah halaman
    isi.addEventListener('click', function (e) {
        var batal = e.target.closest ? e.target.closest('.btn-secondary') : null;
        if (batal) { e.preventDefault(); tutup(); }
    });

    // preview foto untuk form di dalam modal
    isi.addEventListener('change', function (e) {
        if (e.target.id !== 'foto') { return; }
        var img = isi.querySelector('#fotoPreview');
        if (!img) { return; }
        if (e.target.files && e.target.files[0]) {
            img.src = URL.createObjectURL(e.target.files[0]);
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
        }
    });
})();