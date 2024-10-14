// admin_login.js

document.getElementById('manager-login-btn').addEventListener('click', function() {
    document.querySelector('.login-box').classList.add('flipped');
});

document.getElementById('admin-login-btn').addEventListener('click', function() {
    document.querySelector('.login-box').classList.remove('flipped');
});