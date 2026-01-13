document.addEventListener('DOMContentLoaded', () => {
    // Likes
    document.querySelectorAll('.btn-like').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch('/image/like/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => {
                    if (res.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    return res.json();
                })
                .then(data => {
                    if (data?.numLikes !== undefined) {
                        document.getElementById('likes-' + id).textContent = data.numLikes;
                    }
                });
        });
    }); 

    // Descargas
    document.querySelectorAll('.btn-download').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const fileUrl = this.dataset.file;

            if (confirm("¿Quieres descargar esta imagen?")) {
                fetch('/image/download/' + id, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('downloads-' + id).textContent = data.numDownloads;

                        const a = document.createElement('a');
                        a.href = fileUrl;
                        a.download = fileUrl.split('/').pop();
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    });
            }
        });
    });


    // Opcional: aumentar vistas cuando se carga la imagen (o cuando la veas)
    document.querySelectorAll('.image-item img').forEach(img => {
        const id = img.closest('.image-item').querySelector('.btn-like').dataset.id;
        fetch(`/image/view/${id}`, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
            .then(res => res.json())
            .then(data => {
                if (data.numViews !== undefined) {
                    img.closest('.image-item').querySelector('.num-views').textContent = data.numViews;
                }
            });
    });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(){
        const id = this.dataset.id;
        if(confirm("¿Quieres borrar esta imagen permanentemente?")){
            fetch("/image/delete/" + id, { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'} })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        // Borrar del DOM
                        this.closest('.image-item').remove();
                    } else {
                        alert("No se pudo borrar la imagen.");
                    }
                });
        }
    });
});