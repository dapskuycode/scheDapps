function addTasks() {
    if (isLocked('Tasks')) {
        alert("Tasks are locked. Toggle edit mode to make changes.");
        return;
    }

    const taskName = document.getElementById('taskName').value; // Hapus backtick jika tidak ada variabel
    const taskDesc = document.getElementById('taskDesc').value;
    const taskDL = document.getElementById('taskDL').value;
    const timeDL = document.getElementById('timeDL').value;

    if (taskName === '' || taskDL === '' || timeDL === '') {
        alert("Some input is missing");
        return;
    }

    const formData = new FormData();
    formData.append('taskName', taskName);
    formData.append('taskDesc', taskDesc);
    formData.append('taskDL', taskDL);
    formData.append('timeDL', timeDL);

    fetch('taskBackEnd/addTask.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            // Coba dapatkan pesan error dari server jika ada
            return response.text().then(text => {
                // Jika server mengirim JSON error, coba parse
                try {
                    const errData = JSON.parse(text);
                    throw new Error(errData.message || text || 'Server error');
                } catch (e) {
                    throw new Error(text || 'Server error');
                }
            });
        }
        return response.json(); // Harapkan JSON dari server
    })
    .then(data => {
        console.log("Add Task Response:", data);
        if (data.success) { // Jika PHP mengirim {"success": true, ...}
            alert(data.message || 'Task added successfully');

            // SEMUA MANIPULASI DOM DAN PEMBERSIHAN INPUT ADA DI SINI
            const li = document.createElement('li');
            li.innerHTML = `${taskName} - ${taskDL} - ${timeDL}`; // Isi sebelum appendChild button

            const p = document.createElement('p');
            p.className = 'task-description';
            p.innerHTML = `Description: ${taskDesc}`;

            const btn = document.createElement('button'); // Tanpa backtick
            btn.textContent = 'DONE';
            btn.onclick = function() {
                if (isLocked('Tasks')) {
                    alert("Tasks are locked. Toggle edit mode to make changes.");
                    return;
                }
                li.remove();
                p.remove();
                // Anda mungkin juga ingin memanggil fungsi untuk menghapus task dari database di sini
            };

            li.appendChild(btn); // Tambahkan tombol ke li
            document.getElementById('listTasks').append(li);
            document.getElementById('listTasks').append(p);

            // Kosongkan input fields SETELAH berhasil
            document.getElementById('taskName').value = '';
            document.getElementById('taskDesc').value = '';
            document.getElementById('taskDL').value = '';
            document.getElementById('timeDL').value = '';

        } else {
            alert('Failed to add task: ' + (data.message || 'Unknown server error'));
        }
    })
    .catch(error => {
        console.error('Error during addTasks fetch:', error);
        alert('An error occurred while adding task: ' + error.message);
    });
}

async function loadTask(){
    try {
        const response = await fetch('taskBackEnd/getTask.php'); // Panggil getTask.php
        if (!response.ok) {
            // Tangani jika status HTTP bukan 2xx
            const errorData = await response.text(); // Coba dapatkan teks error jika ada
            throw new Error('Gagal memuat task: ' + response.statusText + ' - ' + errorData);
        }
        const result = await response.json();

        if (result.success && result.data) {
            renderTasks(result.data); // Kirim data ke fungsi render
        } else {
            console.error('Failed to load tasks:', result.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Error during loadTask fetch:', error);
        alert('An error occurred while loading tasks: ' + error.message);
    }

}

function renderTasks(tasks) {
    
    console.log('Data received by renderTasks:', tasks);

    const listTasks = document.getElementById('listTasks');
    listTasks.innerHTML = ''; // Kosongkan daftar sebelum menambahkan item baru

    tasks.forEach(task => {
        const li = document.createElement('li');
        li.innerHTML = `${task.task_name} - ${task.due_date} - ${task.due_time}`;

        const p = document.createElement('p');
        p.className = 'task-description';
        p.innerHTML = `Description: ${task.description}`;

        const btn = document.createElement('button');
        btn.textContent = 'DONE';
        btn.onclick = function() {
            console.log('[KLIK TOMBOL DONE] Mencoba menghapus task dengan ID:', task.id, 'Tipe:', typeof task.id);
            if (isLocked('Tasks')) {
                alert("Tasks are locked. Toggle edit mode to make changes.");
                return;
            }
            deleteTask(task.id); // Panggil fungsi untuk menghapus task dari database
            li.remove();
            p.remove();
            
        };

        li.appendChild(btn);
        listTasks.append(li);
        listTasks.append(p);
    });
}

async function deleteTask(taskId) {
    console.log("Task ID to delete:", taskId, "Type:", typeof taskId);
    if (isLocked('Tasks')) {
        alert("Tasks are locked. Toggle edit mode to make changes.");
        return;
    }
    const formData = new FormData();
    formData.append('id', taskId); // <--- PERBAIKAN: 'taskId' diubah menjadi 'id'

    try {
        const response = await fetch('taskBackEnd/delTask.php', { // Pastikan path ini benar
            method: 'POST',
            body: formData
        });

        // Coba untuk selalu mendapatkan body respons untuk debugging, lalu parse sebagai JSON jika response.ok
        const responseText = await response.text(); // Dapatkan respons sebagai teks dulu

        if (!response.ok) {
            // Jika server mengirim JSON error, coba parse
            try {
                const errData = JSON.parse(responseText);
                // Gunakan pesan error dari server jika ada, jika tidak gunakan teks respons mentah
                throw new Error(errData.message || responseText || `Server error: ${response.status}`);
            } catch (e) {
                // Jika respons bukan JSON (misalnya HTML error), tampilkan teks respons mentah
                throw new Error(responseText || `Server error: ${response.status}`);
            }
        }

        // Jika response.ok, kita harapkan JSON yang valid
        const result = JSON.parse(responseText); 

        if (result.success) {
            alert(result.message || 'Task deleted successfully');
            loadTask(); // Muat ulang daftar tugas setelah penghapusan
        } else {
            // Pesan error dari JSON yang dikirim server (misal, task tidak ditemukan)
            alert('Failed to delete task: ' + (result.message || 'Unknown error from server.'));
        }
    } catch (error) {
        console.error('Error during deleteTask fetch:', error);
        // error.message akan berisi pesan yang kita buat di atas
        alert('An error occurred while deleting task: ' + error.message);
    }
}