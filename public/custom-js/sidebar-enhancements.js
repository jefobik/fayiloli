// Sidebar Enhancements: Drag-and-drop, Sorting, Dark Mode Toggle
// Requires: SortableJS (https://sortablejs.github.io/Sortable/)

document.addEventListener('DOMContentLoaded', function () {
    // --- DRAG & DROP for .folders ---
    const foldersList = document.querySelector('.folders');
    if (foldersList && window.Sortable) {
        window.sidebarSortable = Sortable.create(foldersList, {
            animation: 180,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                // TODO: Persist new order to backend via AJAX if needed
                // Example: sendOrderToServer([...foldersList.children].map(li => li.dataset.id));
            }
        });
    }

    // --- SORTING BUTTONS ---
    const workspaceLabel = document.querySelector('.sidebar-section-label[style*="Workspaces"]');
    if (workspaceLabel) {
        const sortBtn = document.createElement('button');
        sortBtn.className = 'sidebar-sort-btn';
        sortBtn.title = 'Sort folders';
        sortBtn.innerHTML = '<span id="sortAsc">&#8593;</span><span id="sortDesc" style="display:none">&#8595;</span>';
        sortBtn.style = 'background:none;border:none;cursor:pointer;margin-left:0.5rem;color:#7c3aed;font-size:1rem;';
        workspaceLabel.appendChild(sortBtn);
        let asc = true;
        sortBtn.addEventListener('click', function () {
            const items = Array.from(foldersList.children);
            items.sort((a, b) => {
                const textA = a.textContent.trim().toLowerCase();
                const textB = b.textContent.trim().toLowerCase();
                return asc ? textA.localeCompare(textB) : textB.localeCompare(textA);
            });
            items.forEach(li => foldersList.appendChild(li));
            asc = !asc;
            document.getElementById('sortAsc').style.display = asc ? '' : 'none';
            document.getElementById('sortDesc').style.display = asc ? 'none' : '';
        });
    }

});
