function login() {
    const username = document.getElementById('usernameLogin').value;
    const password = document.getElementById('passwordLogin').value;

    if (!username || !password) {
        alert('Please enter both username and password');
        return;
    }

    const formData = new FormData();
    formData.append('usernameLogin', username);
    formData.append('passwordLogin', password);

    fetch('log/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log("Login Response:", data); 
        if (data.trim() === 'success') {
            document.getElementById('mainPage').classList.remove('pageHidden');
            document.getElementById('authPage').classList.add('pageHidden');
            showSection('Weekly');
            loadWeeklySchedule(); 
            console.log('Login successful, session started.');
        } else {
            alert('Login gagal: ' + data);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Terjadi kesalahan jaringan saat login.' + error.message);
    });

    document.getElementById('usernameLogin').value = '';
    document.getElementById('passwordLogin').value = '';
}

async function logout() {
    try {
        const response = await fetch('log/logout.php', { 
            method: 'POST' 
        });
        const result = await response.json(); // logout.php kita set untuk kirim JSON

        if (result.success) {
            console.log('Server session destroyed successfully.');
        } else {
            // Meskipun logout di server gagal (jarang terjadi jika kodenya benar),
            // kita mungkin tetap ingin melanjutkan logout di sisi klien.
            console.warn('Server logout may have failed:', result.message);
        }

    } catch (error) {
        console.error('Error fetching logout.php:', error);
        // Tetap lanjutkan logout di sisi klien meskipun ada error jaringan ke server
    }

    // 2. Setelah (mencoba) menghancurkan session di server, lakukan perubahan di sisi klien
    localStorage.removeItem('loggedIn'); // Hapus dari localStorage

    // Sembunyikan halaman utama dan tampilkan halaman autentikasi
    const mainPage = document.getElementById('mainPage');
    const authPage = document.getElementById('authPage');
    if (mainPage) mainPage.classList.add('pageHidden');
    if (authPage) authPage.classList.remove('pageHidden');

    // (Opsional) Arahkan kembali ke tampilan form login jika ada toggle login/signup
    const loginPage = document.getElementById('loginPage');
    const signUpPage = document.getElementById('signUpPage');
    const authToggle = document.getElementById('authToggle');

    if (loginPage) loginPage.style.display = 'block'; // Tampilkan form login
    if (signUpPage) signUpPage.style.display = 'none';  // Sembunyikan form signup
    if (authToggle) authToggle.checked = false;         // Reset toggle ke posisi login
}

function signUp() {
    const username = document.getElementById('usernameSignUp').value;
    const password = document.getElementById('passwordSignUp').value;
    const confirmPassword = document.getElementById('confirmPasswordSignUp').value;

    if (!username || !password || !confirmPassword) {
        alert('Semua kolom wajib diisi.');
        return;
    }

    if (password !== confirmPassword) {
        alert('Konfirmasi password tidak cocok.');
        return;
    }

    const formData = new FormData();
    formData.append('usernameSignUp', username);
    formData.append('passwordSignUp', password);

    fetch('log/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log("Register Response:", data);
        if (data.trim() === 'success') {
            alert('Registrasi berhasil!');
            // Tambahkan redirect atau buka halaman login
        } else {
            alert('Registrasi gagal: ' + data);
        }
    })
    .catch(error => {
        console.error('Register error:', error);
        alert('Terjadi kesalahan saat mendaftar.');
    });

    // Kosongkan input
    document.getElementById('usernameSignUp').value = '';
    document.getElementById('passwordSignUp').value = '';
    document.getElementById('confirmPasswordSignUp').value = '';
}