const sidebar = document.querySelector('#sidebar');
const sidebarToggle = document.querySelector('#sidebar-toggle');
const sidebarBackdrop = document.querySelector('#sidebar-backdrop');

const setSidebarOpen = (isOpen) => {
    sidebar?.classList.toggle('-translate-x-full', !isOpen);
    sidebarBackdrop?.classList.toggle('hidden', !isOpen);
};

sidebarToggle?.addEventListener('click', () => setSidebarOpen(true));
sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));

const taskModal = document.querySelector('#task-modal');
const taskListInput = document.querySelector('#task-list-id');
const taskTitleInput = document.querySelector('#task-title');

document.querySelectorAll('[data-open-task-modal]').forEach((button) => {
    button.addEventListener('click', () => {
        if (taskListInput) {
            taskListInput.value = button.dataset.listId;
        }

        taskModal?.showModal();
        window.setTimeout(() => taskTitleInput?.focus(), 80);
    });
});

document.querySelectorAll('[data-close-task-modal]').forEach((button) => {
    button.addEventListener('click', () => taskModal?.close());
});

taskModal?.addEventListener('click', (event) => {
    if (event.target === taskModal) {
        taskModal.close();
    }
});

const toast = document.querySelector('[data-toast]');
if (toast) {
    window.setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        window.setTimeout(() => toast.remove(), 200);
    }, 3200);
}

const chatMessages = document.querySelector('#chat-messages');
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

document.querySelectorAll('[data-open-dialog]').forEach((button) => {
    button.addEventListener('click', () => document.querySelector(`#${button.dataset.openDialog}`)?.showModal());
});

document.querySelectorAll('[data-close-dialog]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

document.querySelectorAll('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
});

const memberForm = document.querySelector('#member-form');
const memberTeamSelect = document.querySelector('#member-team-select');
const updateMemberFormAction = () => {
    if (memberForm && memberTeamSelect) {
        memberForm.action = memberForm.dataset.actionTemplate.replace('__TEAM__', memberTeamSelect.value);
    }
};
memberTeamSelect?.addEventListener('change', updateMemberFormAction);
updateMemberFormAction();

const dashboardSearch = document.querySelector('#dashboard-search');
dashboardSearch?.addEventListener('input', () => {
    const query = dashboardSearch.value.trim().toLocaleLowerCase('id');
    document.querySelectorAll('[data-dashboard-card]').forEach((card) => {
        card.classList.toggle('hidden', query !== '' && !card.dataset.search.includes(query));
    });
});

const searchInput = document.querySelector('#task-search');
searchInput?.addEventListener('input', () => {
    const query = searchInput.value.trim().toLocaleLowerCase('id');

    document.querySelectorAll('[data-task-card]').forEach((card) => {
        const title = card.querySelector('.task-title')?.textContent?.toLocaleLowerCase('id') ?? '';
        card.classList.toggle('hidden', query !== '' && !title.includes(query));
    });
});

let draggedCard = null;

const updateCounts = () => {
    document.querySelectorAll('.kanban-column').forEach((column) => {
        const count = column.querySelectorAll('[data-task-card]').length;
        const badge = column.querySelector('[data-task-count]');

        if (badge) {
            badge.textContent = count;
        }
    });
};

document.querySelectorAll('[data-task-card]').forEach((card) => {
    card.addEventListener('dragstart', () => {
        draggedCard = card;
        card.classList.add('is-dragging');
    });

    card.addEventListener('dragend', () => {
        card.classList.remove('is-dragging');
        document.querySelectorAll('.task-dropzone').forEach((zone) => zone.classList.remove('is-over'));
        draggedCard = null;
    });
});

document.querySelectorAll('.task-dropzone').forEach((dropzone) => {
    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-over');

        if (draggedCard) {
            const cards = [...dropzone.querySelectorAll('[data-task-card]:not(.is-dragging)')];
            const nextCard = cards.find((card) => event.clientY <= card.getBoundingClientRect().top + card.offsetHeight / 2);
            dropzone.insertBefore(draggedCard, nextCard ?? null);
        }
    });

    dropzone.addEventListener('dragleave', (event) => {
        if (!dropzone.contains(event.relatedTarget)) {
            dropzone.classList.remove('is-over');
        }
    });

    dropzone.addEventListener('drop', async (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-over');

        if (!draggedCard) {
            return;
        }

        const position = [...dropzone.querySelectorAll('[data-task-card]')].indexOf(draggedCard);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const response = await fetch(draggedCard.dataset.updateUrl, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ board_list_id: Number(dropzone.dataset.listId), position }),
            });

            if (!response.ok) {
                throw new Error('Gagal memperbarui tugas.');
            }

            updateCounts();
        } catch (error) {
            window.alert(error.message);
            window.location.reload();
        }
    });
});
