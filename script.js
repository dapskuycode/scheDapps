

window.onload = function() {
    if (localStorage.getItem('loggedIn') === 'true') {
        document.getElementById('loginPage').classList.add('pageHidden');
        document.getElementById('mainPage').classList.remove('pageHidden');
    }
    
    toggleEditMode('Weekly');
    toggleEditMode('Events');
    toggleEditMode('Tasks');
};

function isLocked(section) {
    return document.getElementById(`locking${section}`).checked;
}

function toggleEditMode(section) {
    const isEditLocked = document.getElementById(`locking${section}`).checked;
    const inputs = document.querySelectorAll(`.${section.toLowerCase()}-inputs`);
    
    inputs.forEach(inputGroup => {
        if (isEditLocked) {
            inputGroup.style.display = 'none';
        } else {
            inputGroup.style.display = 'grid';
        }
    });
    
    const listId = `list${section === 'Weekly' ? '' : section}`;
    const parentElement = document.getElementById(section);
    const allLists = section === 'Weekly' 
        ? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
            .map(day => document.getElementById(`list${day}`))
        : [document.getElementById(listId)];
    
    allLists.forEach(list => {
        if (list) {
            const buttons = list.querySelectorAll('button');
            buttons.forEach(button => {
                button.style.display = isEditLocked ? 'none' : 'inline-block';
            });
        }
    });
}

function addEvent() {
    if (isLocked('Events')) {
        alert("Events are locked. Toggle edit mode to make changes.");
        return;
    }
    
    const eventName = document.getElementById(`eventName`).value;
    const eventDate = document.getElementById(`eventDate`).value;
    const eventTime = document.getElementById(`eventTime`).value;
    
    if (eventDate === '' || eventName === '') {
        alert("Some input is missing");
        return;
    }

    const li = document.createElement('li');
    li.innerHTML = `${eventName} - ${eventDate} - ${eventTime}`;

    const btn = document.createElement(`button`);
    btn.textContent = 'DELETE';
    btn.onclick = function() {
        if (isLocked('Events')) {
            alert("Events are locked. Toggle edit mode to make changes.");
            return;
        }
        li.remove();
    }
    li.appendChild(btn);

    document.getElementById(`listEvents`).append(li);

    document.getElementById(`eventName`).value = '';
    document.getElementById(`eventDate`).value = '';
    document.getElementById(`eventTime`).value = '';
}

function addTasks() {
    if (isLocked('Tasks')) {
        alert("Tasks are locked. Toggle edit mode to make changes.");
        return;
    }
    
    const taskName = document.getElementById(`taskName`).value;
    const taskDesc = document.getElementById(`taskDesc`).value;
    const taskDL = document.getElementById(`taskDL`).value;
    const timeDL = document.getElementById(`timeDL`).value;

    if (taskName === '' || taskDL === '' || timeDL === '') {
        alert("Some input is missing");
        return;
    }

    const li = document.createElement('li');
    li.innerHTML = `${taskName} - ${taskDL} - ${timeDL}`;

    const p = document.createElement('p');
    p.className = 'task-description';
    p.innerHTML = `Description: ${taskDesc}`;

    const btn = document.createElement(`button`);
    btn.textContent = 'DONE';
    btn.onclick = function() {
        if (isLocked('Tasks')) {
            alert("Tasks are locked. Toggle edit mode to make changes.");
            return;
        }
        li.remove();
        p.remove();
    }

    li.appendChild(btn);
    document.getElementById('listTasks').append(li);
    document.getElementById('listTasks').append(p);

    document.getElementById(`taskName`).value = '';
    document.getElementById(`taskDesc`).value = '';
    document.getElementById(`taskDL`).value = '';
    document.getElementById(`timeDL`).value = '';
}

function showSection(section) {
    const sections = document.querySelectorAll(`.Section`);
    
    for (let i = 0; i < sections.length; i++) {
        sections[i].classList.add(`hidden`);
    }
    if(section === 'Weekly') {
        loadWeeklySchedule();
    }
    
    const show = document.getElementById(section);
    show.classList.remove(`hidden`);
}

document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const loginPage = document.getElementById('loginPage');
    const signUpPage = document.getElementById('signUpPage');
    const toggleCheckbox = document.querySelector('input[type="checkbox"]');
    
    // Initially hide signUpPage and show loginPage
    signUpPage.classList.add('pageHidden');
    loginPage.classList.remove('pageHidden');
    
    // Toggle between login and signup pages
    toggleCheckbox.addEventListener('change', function() {
        if (this.checked) {
            loginPage.classList.add('pageHidden');
            signUpPage.classList.remove('pageHidden');
        } else {
            signUpPage.classList.add('pageHidden');
            loginPage.classList.remove('pageHidden');
        }
    });
});




