const searchInput = document.getElementById('search');
const results = document.getElementById('results');
const addForm = document.getElementById('addForm');
const formMessage = document.getElementById('formMessage');

let requestCounter = 0;

async function request(url, options) {
    const response = await fetch(url, options);
    let data = null;
    try {
        data = await response.json();
    } catch (e) {
    }
    if (!response.ok) {
        throw new Error((data && data.error) || 'Помилка сервера (' + response.status + ')');
    }
    return data;
}

function createRow(topic) {
    const tr = document.createElement('tr');
    const values = [
        topic.id,
        topic.title,
        topic.author,
        topic.created_at,
        Number(topic.is_pinned) ? 'Так' : 'Ні',
    ];
    values.forEach((value) => {
        const td = document.createElement('td');
        td.textContent = value;
        tr.appendChild(td);
    });
    return tr;
}

function showMessageRow(text, isError) {
    results.innerHTML = '';
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = 5;
    td.textContent = text;
    if (isError) {
        td.className = 'error';
    }
    tr.appendChild(td);
    results.appendChild(tr);
}

function renderRows(topics) {
    results.innerHTML = '';
    if (topics.length === 0) {
        showMessageRow('Тем не знайдено', false);
        return;
    }
    topics.forEach((topic) => results.appendChild(createRow(topic)));
}

async function loadList(query = '') {
    const currentRequest = ++requestCounter;
    try {
        const url = 'api_list.php' + (query ? '?q=' + encodeURIComponent(query) : '');
        const topics = await request(url);
        if (currentRequest === requestCounter) {
            renderRows(topics);
        }
    } catch (error) {
        if (currentRequest === requestCounter) {
            showMessageRow('Не вдалося завантажити список: ' + error.message, true);
        }
    }
}

searchInput.addEventListener('input', () => {
    loadList(searchInput.value.trim());
});

addForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    formMessage.textContent = '';
    formMessage.className = '';

    const payload = {
        title: document.getElementById('title').value.trim(),
        author: document.getElementById('author').value.trim(),
        is_pinned: document.getElementById('isPinned').checked,
    };

    try {
        await request('api_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        addForm.reset();
        formMessage.textContent = 'Тему додано';
        formMessage.className = 'success';
        loadList(searchInput.value.trim());
    } catch (error) {
        formMessage.textContent = 'Не вдалося додати тему: ' + error.message;
        formMessage.className = 'error';
    }
});

loadList();