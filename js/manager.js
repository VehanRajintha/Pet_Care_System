document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const type = this.dataset.type;

        document.getElementById('editId').value = id;

        if (type === 'user') {
            document.getElementById('userFields').style.display = 'block';
            document.getElementById('petFields').style.display = 'none';
        } else {
            document.getElementById('userFields').style.display = 'none';
            document.getElementById('petFields').style.display = 'block';
        }

        document.getElementById('editModal').style.display = 'flex';
    });
});

document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('editModal').style.display = 'none';
});

window.onclick = function(event) {
    if (event.target === document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
};

function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });

    document.getElementById(sectionId).classList.add('active');
}


