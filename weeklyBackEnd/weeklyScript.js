function addTask(day) {
    if (isLocked('Weekly')) {
        alert("The schedule is locked. Toggle edit mode to make changes.");
        return;
    }
    
    const act = document.getElementById(`act${day}`).value;
    const timeStart = document.getElementById(`timeStart${day}`).value;
    const timeEnd = document.getElementById(`timeEnd${day}`).value;
    const loc = document.getElementById(`loc${day}`).value;

    if (act === '' || timeStart === '' || timeEnd === '' || loc === '') {
        alert('Please fill all the content');
        return;
    }

    const formData = new FormData();
    formData.append('act', act);
    formData.append('timeStart', timeStart);
    formData.append('timeEnd', timeEnd);
    formData.append('loc', loc);
    formData.append('day', day);
    fetch('weeklyBackEnd/addWeekly.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log("Add Task Response:", data); 
        if (data.trim() === 'success') {
            alert('Task added successfully');
            const li = document.createElement('li');
            li.innerHTML = `${act} - ${timeStart} - ${timeEnd} - ${loc}`;

            const btn = document.createElement('button');
            btn.textContent = 'DELETE';
            btn.onclick = function() {
                if (isLocked('Weekly')) {
                    alert("The schedule is locked. Toggle edit mode to make changes.");
                    return;
                }
                li.remove();
            };

            li.appendChild(btn);
            document.getElementById(`list${day}`).append(li);

            document.getElementById(`act${day}`).value = '';
            document.getElementById(`timeStart${day}`).value = '';
            document.getElementById(`timeEnd${day}`).value = '';
            document.getElementById(`loc${day}`).value = '';

            loadWeeklySchedule(); // Reload the schedule to reflect changes
        } else {
            alert('Failed to add task: ' + data);
        }
    })
}

// Di file JavaScript Anda

// Fungsi untuk memuat jadwal dari server
async function loadWeeklySchedule() {
    try {
        const response = await fetch('weeklyBackEnd/getWeekly.php'); // Panggil getWeekly.php
        if (!response.ok) {
            // Tangani jika status HTTP bukan 2xx
            const errorData = await response.text(); // Coba dapatkan teks error jika ada
            throw new Error('Gagal memuat jadwal: ' + response.statusText + ' - ' + errorData);
        }
        const result = await response.json();

        if (result.success && result.data) {
            renderWeeklySchedule(result.data); // Kirim data ke fungsi render
        } else {
            console.warn('Tidak ada data jadwal atau terjadi kesalahan: ' + (result.message || 'Data kosong'));
            renderWeeklySchedule([]); // Render list kosong jika tidak ada data atau error
        }
    } catch (error) {
        console.error('Error loading weekly schedule:', error);
        alert('Tidak dapat memuat jadwal. ' + error.message);
        renderWeeklySchedule([]); // Pastikan list dikosongkan jika ada error
    }
}

// Fungsi untuk menampilkan (render) data jadwal ke HTML
function renderWeeklySchedule(scheduleData) {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    // 1. Kosongkan semua list jadwal yang sudah ada di HTML
    days.forEach(day => {
        const listElement = document.getElementById(`list${day}`);
        if (listElement) {
            listElement.innerHTML = '';
        }
    });

    // 2. Loop melalui data jadwal dan tambahkan ke list yang sesuai
    scheduleData.forEach(item => {
        const listElement = document.getElementById(`list${item.day_of_week}`); // item.day dari database
        if (listElement) {
            const li = document.createElement('li');
            // Tampilkan data jadwal (sesuaikan formatnya)
            // Anda bisa menyimpan item.id di dataset li jika akan digunakan untuk delete nanti
            li.dataset.scheduleId = item.id; 
            
            let itemText = `${item.time_start}`;
            if (item.time_end) {
                itemText += ` - ${item.time_end}`;
            }
            itemText += `: ${item.activity}`;
            if (item.location) {
                itemText += ` (@ ${item.location})`;
            }
            li.textContent = itemText; // Lebih aman menggunakan textContent jika tidak ada HTML di dalamnya

            // Buat tombol delete (untuk nanti saat delWeekly.php sudah siap)
            const btnDelete = document.createElement('button');
            btnDelete.textContent = 'DELETE';
            btnDelete.onclick = function() {
                if (isLocked('Weekly')) {
                    alert("The schedule is locked. Toggle edit mode to make changes.");
                    return;
                }
                // Konfirmasi sebelum menghapus
                if(confirm(`Yakin ingin menghapus aktivitas "${item.activity}"?`)) {
                    // Panggil fungsi untuk menghapus dari database, misal:
                    deleteWeeklyActivity(item.id); 
                    console.log('Akan menghapus item dengan ID:', item.id); // Placeholder
                }
            };
            // Sesuaikan style tombol delete jika perlu
            btnDelete.style.backgroundColor = '#cf6679'; 
            btnDelete.style.marginLeft = '10px';

            li.appendChild(btnDelete);
            listElement.appendChild(li);
        }
    });
}

async function deleteWeeklyActivity(itemId) {
    if (!itemId) {
        alert('ID item tidak valid untuk dihapus.');
        return;
    }

    const formData = new FormData();
    formData.append('id', itemId);

    try {
        const response = await fetch('weeklyBackEnd/delWeekly.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            // Jika status HTTP bukan 2xx, coba dapatkan detail error
            const errorData = await response.text(); // atau response.json() jika server selalu mengirim JSON
            throw new Error(`Gagal menghapus item: ${response.statusText} - ${errorData}`);
        }

        const result = await response.json();
        console.log("Delete Task Response:", result);

        if (result.success) {
            alert(result.message || 'Item berhasil dihapus.');
            loadWeeklySchedule(); // Muat ulang jadwal untuk merefleksikan perubahan
        } else {
            alert('Gagal menghapus item: ' + (result.message || 'Terjadi kesalahan yang tidak diketahui.'));
        }
    } catch (error) {
        console.error('Error deleting weekly activity:', error);
        alert('Tidak dapat menghapus item. ' + error.message);
    }
}