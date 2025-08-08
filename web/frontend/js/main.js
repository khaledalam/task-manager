let currentPage = 1;           // 1-based
let pageSize = 20;             // default matches the <select>
let totalPages = null;         // when backend returns _meta
let lastPageHadFullItems = false; // fallback when no _meta

function changePageSize(v) {
    pageSize = parseInt(v, 10) || 20;
    currentPage = 1;
    loadTasks();
}


function nextPage() {
    if (totalPages && currentPage >= totalPages) return;
    if (!totalPages && lastPageHadFullItems === false) return;
    currentPage++;
    loadTasks();
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadTasks();
    }
}

function updatePageIndicator(meta, itemsLength) {
    if (meta) {
        currentPage = meta.currentPage || currentPage;
        totalPages  = meta.pageCount || null;
        document.getElementById('pageIndicator').textContent =
            `Page ${currentPage} of ${totalPages}`;
    } else {
        // No meta: show simple page number and infer "has next" by page fill
        lastPageHadFullItems = (itemsLength === pageSize);
        totalPages = null;
        document.getElementById('pageIndicator').textContent =
            `Page ${currentPage}`;
    }
}


function viewTask(task) {
    document.getElementById('modalTaskTitle').textContent = task.title || '';
    document.getElementById('modalTaskDescription').textContent = task.description || '';
    document.getElementById('modalTaskStatus').textContent = task.status || '';
    document.getElementById('modalTaskPriority').textContent = task.priority || '';
    document.getElementById('modalTaskDueDate').textContent = task.due_date || 'N/A';
    document.getElementById('modalTaskCreated').textContent = task.created_at || 'N/A';
    document.getElementById('modalTaskTags').textContent = (task.tags || []).map(t => t.name).join(', ');


    const statusBadge = document.getElementById('modalTaskStatus');
    statusBadge.className = 'badge';
    if (task.status === 'pending') statusBadge.classList.add('bg-secondary');
    else if (task.status === 'in_progress') statusBadge.classList.add('bg-warning');
    else if (task.status === 'completed') statusBadge.classList.add('bg-success');

    const priorityBadge = document.getElementById('modalTaskPriority');
    priorityBadge.className = 'badge';
    if (task.priority === 'low') priorityBadge.classList.add('bg-secondary');
    else if (task.priority === 'medium') priorityBadge.classList.add('bg-info');
    else if (task.priority === 'high') priorityBadge.classList.add('bg-danger');

    const modal = new bootstrap.Modal(document.getElementById('viewTaskModal'));
    modal.show();
}

async function restoreTask(id) {
    try {
        await axios.patch(`/tasks/${id}/restore`);
        loadTasks();
    } catch (err) {
        alert("❌ Failed to restore task.");
    }
}



async function loadTasks() {
    const status = document.getElementById('filterStatus').value;
    const priority = document.getElementById('filterPriority').value;
    const q = document.getElementById('searchInput').value;
    const sort = document.getElementById('sortBy').value;

    const due_date_from = document.getElementById('filterDueFrom').value;
    const due_date_to = document.getElementById('filterDueTo').value;

    const showDeleted = document.getElementById('showDeletedToggle').checked;

    const tag = document.getElementById('filterTag').value;

    const params = {
        status,
        priority,
        q,
        sort,
        order: 'desc',
        limit: pageSize,
        offset: (currentPage - 1) * pageSize,
        due_date_from,
        due_date_to,
        show_deleted: showDeleted ? 1 : 0,
        tag,
        _t: Date.now()
    };

    const res = await axios.get('/tasks', { params, headers: { 'Cache-Control': 'no-cache' } });
    const payload = res.data;
    const items = (payload.items || payload) || [];
    const list = document.getElementById('taskList');
    list.innerHTML = '';

    items.forEach(task => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        const isDeleted = !!task.deleted_at;
        li.innerHTML = `
      <div>
        <strong>${task.title}</strong>
        <div class="text-muted small">
          ${task.description || ''}
          ${task.tags?.length ? '<br><span class="badge bg-secondary">' + task.tags.map(t => t.name).join('</span> <span class="badge bg-secondary">') + '</span>' : ''}
          ${isDeleted ? '<br><span class="badge bg-secondary mt-1">Deleted</span>' : ''}
        </div>
      </div>
      <div>
        ${isDeleted
            ? `<button class="btn btn-sm btn-success me-1" onclick="restoreTask(${task.id})">Restore</button>`
            : `
            <button class="btn btn-sm btn-outline-info me-1" onclick='viewTask(${JSON.stringify(task)})'>View</button>
            <button class="btn btn-sm btn-outline-primary me-1" onclick='editTask(${JSON.stringify(task)})'>Edit</button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(${task.id})">Delete</button>
          `}
      </div>
    `;
        list.appendChild(li);
    });

    // ---- pagination state updates ----
    const meta = payload._meta || null; // Yii REST serializer meta
    if (meta) {
        totalPages = meta.pageCount || null;
        // meta.currentPage is usually 1-based; keep our currentPage in sync
        currentPage = meta.currentPage || currentPage;
    } else {
        totalPages = null;
        lastPageHadFullItems = (items.length === pageSize);
    }

    // indicator + buttons (if you have them)
    const indicator = document.getElementById('pageIndicator');
    if (indicator) {
        indicator.textContent = meta ? `Page ${meta.currentPage} of ${meta.pageCount}` : `Page ${currentPage}`;
    }
    const canPrev = currentPage > 1;
    const canNext = meta ? (currentPage < (meta.pageCount || 1)) : lastPageHadFullItems;
    document.querySelectorAll('[data-role="prev"]').forEach(b => b.disabled = !canPrev);
    document.querySelectorAll('[data-role="next"]').forEach(b => b.disabled = !canNext);

}

async function deleteTask(id) {
    if (!confirm("Delete this task?")) return;
    await axios.delete(`/tasks/${id}`);
    loadTasks();
}

function editTask(task) {
    document.getElementById('taskId').value = task.id;
    document.querySelector('[name="title"]').value = task.title;
    document.querySelector('[name="description"]').value = task.description;
    document.querySelector('[name="status"]').value = task.status;
    document.querySelector('[name="priority"]').value = task.priority;
    document.querySelector('[name="due_date"]').value = task.due_date;
    document.querySelector('[name="tags"]').value = (task.tags || []).map(t => t.name).join(', ');
}

function resetForm() {
    document.getElementById('taskForm').reset();
    document.getElementById('taskId').value = '';
    document.getElementById('formMessage').classList.add('d-none');
}

document.getElementById('taskForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form).entries());

    const tags = form.querySelector('[name="tags"]').value;
    data.tags = tags.split(',').map(t => t.trim()).filter(Boolean);


    const id = data.id;
    delete data.id;

    try {
        if (id) {
            await axios.put(`/tasks/${id}`, data);
            showMessage("Task updated ✅", 'success');
        } else {
            await axios.post('/tasks', data);
            showMessage("Task created ✅", 'success');
        }
        resetForm();
        loadTasks();
    } catch (err) {
        const msg = err?.response?.data?.errors
            ? Object.values(err.response.data.errors).flat().join('<br>')
            : '❌ Unknown error';
        showMessage(msg, 'danger');
    }
});

function showMessage(msg, type) {
    const box = document.getElementById('formMessage');
    box.innerHTML = msg;
    box.className = `alert alert-${type}`;
    box.classList.remove('d-none');
}

// Initial Load
loadTasks();

// Bootstrap custom client-side validation
(() => {
    'use strict';
    const form = document.getElementById('taskForm');

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);
})();

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("taskForm");
    const inputs = form.querySelectorAll("input, select, textarea");

    // Validate on input/change
    inputs.forEach(input => {
        input.addEventListener("input", () => validateField(input));
        input.addEventListener("change", () => validateField(input));
    });

    // Field validation function
    function validateField(field) {
        if (field.checkValidity()) {
            field.classList.remove("is-invalid");
            field.classList.add("is-valid");
        } else {
            field.classList.remove("is-valid");
            field.classList.add("is-invalid");
        }
    }

    // On form submit, block if invalid
    form.addEventListener("submit", function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add("was-validated");
    });


    document.getElementById('tagInput').addEventListener('change', function () {
        const raw = this.value;
        const normalized = raw
            .split(',')
            .map(tag => tag.trim())
            .filter(tag => tag.length > 0)
            .join(', ');

        this.value = normalized;
    });

});
