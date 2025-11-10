

<div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->
</div>
<!-- / Layout page -->
</div>

<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout wrapper -->

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('DAssets/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('DAssets/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('DAssets/assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('DAssets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

<script src="{{ asset('DAssets/assets/vendor/js/menu.js') }}"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('DAssets/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('DAssets/assets/js/main.js') }}"></script>

<!-- Page JS -->
<script src="{{ asset('DAssets/assets/js/dashboards-analytics.js') }}"></script>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<script>
  (function () {
    const navBadge = document.getElementById('nav-overdue-count');
    const sidebarBadge = document.getElementById('sidebar-overdue-count');
    const navTrigger = document.getElementById('nav-overdue-trigger');
    const modalEl = document.getElementById('overdueTasksModal');
    const modalCount = document.getElementById('overdue-modal-count');
    const modalLoading = document.getElementById('overdue-modal-loading');
    const modalTableWrapper = document.getElementById('overdue-modal-table-wrapper');
    const modalTableBody = document.getElementById('overdue-modal-table-body');
    const modalMessage = document.getElementById('overdue-modal-message');
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');

    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    const listEndpoint = '{{ route('overdue-tasks.list') }}';
    const summaryEndpoint = '{{ route('overdue-tasks.summary') }}';
    const reminderEndpointTemplate = '{{ route('overdue-tasks.send-reminder', ['task' => '__TASK__']) }}';

    let overdueModalInstance = null;
    if (modalEl && window.bootstrap) {
      overdueModalInstance = new window.bootstrap.Modal(modalEl);
    }

    function setBadgeValue(badgeEl, count) {
      if (!badgeEl) {
        return;
      }
      badgeEl.textContent = count;
      badgeEl.style.display = count > 0 ? 'inline-block' : 'none';
    }

    function showModalMessage(type, message) {
      if (!modalMessage) {
        return;
      }
      modalMessage.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
      modalMessage.classList.add('alert-' + (type || 'info'));
      modalMessage.innerHTML = message;
    }

    function resetModalState() {
      if (modalLoading) {
        modalLoading.classList.remove('d-none');
      }
      if (modalTableWrapper) {
        modalTableWrapper.classList.add('d-none');
      }
      if (modalMessage) {
        modalMessage.classList.add('d-none');
        modalMessage.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
        modalMessage.innerHTML = '';
      }
      if (modalTableBody) {
        modalTableBody.innerHTML = '';
      }
      if (modalCount) {
        modalCount.textContent = '0';
      }
    }

    async function fetchOverdueSummary() {
      try {
        const response = await fetch(summaryEndpoint, { credentials: 'same-origin' });
        const data = await response.json();
        if (!data || !data.success) {
          throw new Error('Failed to load summary');
        }

        const count = Number(data.count || 0);
        setBadgeValue(navBadge, count);
        setBadgeValue(sidebarBadge, count);

        return count;
      } catch (error) {
        console.error('Failed to fetch overdue summary:', error);
        setBadgeValue(navBadge, 0);
        setBadgeValue(sidebarBadge, 0);
        return 0;
      }
    }

    async function fetchOverdueList() {
      try {
        const response = await fetch(listEndpoint + '?per_page=20', { credentials: 'same-origin' });
        const data = await response.json();
        if (!data || !data.success) {
          throw new Error(data && data.message ? data.message : 'Failed to load overdue tasks');
        }
        return data;
      } catch (error) {
        throw error;
      }
    }

    function renderModalTable(list, isManager) {
      if (!modalTableBody) {
        return;
      }

      modalTableBody.innerHTML = '';
      list.forEach(function (task) {
        const actions = [];
        actions.push(
          `<a href="${task.show_url}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show-alt me-1"></i>View</a>`
        );
        if (isManager && task.edit_url) {
          actions.push(
            `<a href="${task.edit_url}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-edit-alt me-1"></i>Edit</a>`
          );
          actions.push(
            `<button type="button" class="btn btn-sm btn-outline-danger js-send-overdue-reminder" data-task-id="${task.id}"><i class="bx bx-mail-send me-1"></i>Send reminder</button>`
          );
        }

        modalTableBody.insertAdjacentHTML('beforeend', `
          <tr>
            <td>
              <div class="fw-semibold text-dark">${task.title}</div>
              <small class="text-muted text-uppercase">${(task.status || '').replace(/_/g, ' ')}</small>
            </td>
            <td>
              <div class="fw-semibold">${task.assignee || 'Unassigned'}</div>
              ${task.assignee_email ? `<small class="text-muted">${task.assignee_email}</small>` : ''}
            </td>
            <td>
              <div class="fw-semibold">${task.project || 'No project'}</div>
              ${task.project_code ? `<small class="text-muted">${task.project_code}</small>` : ''}
            </td>
            <td>
              <div class="fw-semibold text-danger">${task.due_date_for_humans || 'Not set'}</div>
            </td>
            <td>
              <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">${task.overdue_duration || 'N/A'}</span>
            </td>
            <td class="text-end">
              <div class="btn-group" role="group">
                ${actions.join('')}
              </div>
            </td>
          </tr>
        `);
      });

      // Attach reminder handlers
      const reminderButtons = modalTableBody.querySelectorAll('.js-send-overdue-reminder');
      reminderButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          const taskId = this.getAttribute('data-task-id');
          if (!taskId) {
            return;
          }
          let note = prompt('Optional message to include in the overdue reminder (leave blank for default message):');
          if (note === null) {
            return;
          }

          this.disabled = true;
          this.classList.add('disabled');

          window.sendOverdueReminder(taskId, note).finally(() => {
            this.disabled = false;
            this.classList.remove('disabled');
          });
        });
      });
    }

    async function loadOverdueModalData() {
      resetModalState();
      if (!modalLoading) {
        return;
      }

      try {
        const data = await fetchOverdueList();

        if (modalCount) {
          modalCount.textContent = data.total || 0;
        }

        if (!Array.isArray(data.data) || data.data.length === 0) {
          showModalMessage('success', '<i class="bx bx-check-circle me-2"></i>Great job! There are no overdue tasks right now.');
          return;
        }

        renderModalTable(data.data, !!data.manager);
        if (modalTableWrapper) {
          modalTableWrapper.classList.remove('d-none');
        }
      } catch (error) {
        console.error('Failed to load overdue tasks:', error);
        showModalMessage('danger', `<i class="bx bx-error-alt me-2"></i>${error.message || 'Failed to load overdue tasks.'}`);
      } finally {
        if (modalLoading) {
          modalLoading.classList.add('d-none');
        }
      }
    }

    window.sendOverdueReminder = async function (taskId, note) {
      if (!taskId) {
        return;
      }

      try {
        const endpoint = reminderEndpointTemplate.replace('__TASK__', taskId);
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ message: note || '' }),
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error(data && data.message ? data.message : 'Failed to send overdue reminder.');
        }

        alert('Overdue reminder sent successfully.');
        return true;
      } catch (error) {
        console.error('Failed to send overdue reminder:', error);
        alert(error.message || 'Failed to send overdue reminder.');
        return false;
      }
    };

    // Extend global refresh to include overdue summary
    if (typeof window.refreshAllNotifications === 'function') {
      const originalRefresh = window.refreshAllNotifications;
      window.refreshAllNotifications = function () {
        originalRefresh.apply(this, arguments);
        fetchOverdueSummary();
      };
    }

    const shouldPollSummary = Boolean(navBadge || sidebarBadge);
    if (shouldPollSummary) {
      fetchOverdueSummary();
      setInterval(fetchOverdueSummary, 60000);
    }

    if (navTrigger && overdueModalInstance && modalEl) {
      navTrigger.addEventListener('click', function () {
        overdueModalInstance.show();
        loadOverdueModalData();
      });
    }

    if (modalEl) {
      modalEl.addEventListener('show.bs.modal', function () {
        loadOverdueModalData();
      });
    }
  })();
</script>

@stack('scripts')
</body>
</html>
