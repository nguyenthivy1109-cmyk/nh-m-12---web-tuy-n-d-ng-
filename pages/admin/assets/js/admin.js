// Admin Panel Custom JavaScript

// Global functions for company management
function showLoading(button) {
    button.classList.add('loading');
    button.disabled = true;
}

function hideLoading(button) {
    button.classList.remove('loading');
    button.disabled = false;
}

// Confirm dialog with custom styling
function confirmAction(message, callback) {
    if (window.confirm(message)) {
        callback();
    }
}

// AJAX helper function
function ajaxRequest(url, method = 'GET', data = null, successCallback, errorCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            if (successCallback) successCallback(xhr.responseText);
        } else {
            if (errorCallback) errorCallback(xhr.status, xhr.responseText);
        }
    };

    xhr.onerror = function() {
        if (errorCallback) errorCallback(0, 'Network error');
    };

    if (data) {
        const formData = new FormData();
        for (const key in data) {
            formData.append(key, data[key]);
        }
        xhr.send(formData);
    } else {
        xhr.send();
    }
}

// File upload validation
function validateImageFile(file, maxSizeMB = 5) {
    const maxSize = maxSizeMB * 1024 * 1024; // Convert to bytes
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!file) return { valid: true };

    if (file.size > maxSize) {
        return {
            valid: false,
            error: `File size must be less than ${maxSizeMB}MB`
        };
    }

    if (!allowedTypes.includes(file.type)) {
        return {
            valid: false,
            error: 'Only JPG, PNG, GIF, and WebP files are allowed'
        };
    }

    return { valid: true };
}

// Image preview
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const validation = validateImageFile(file);

        if (!validation.valid) {
            alert(validation.error);
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                showLoading(submitBtn);
            }
        });
    });
});

// Enhanced DataTables initialization
$(document).ready(function() {
    // Initialize all DataTables with Vietnamese language
    $('.table').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json"
                },
                "pageLength": 25,
                "order": [[ 0, "desc" ]],
                "responsive": true,
                "initComplete": function() {
                    // Add search input styling
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_filter input').attr('placeholder', 'Tìm kiếm...');
                }
            });
        }
    });
});

// Modal management
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }
}

// Account management functions
function changeRole(accountId, currentRole) {
    const newRole = prompt('Nhập vai trò mới (1: Admin, 2: Nhà tuyển dụng, 3: Ứng viên):', currentRole);
    if (newRole && [1, 2, 3].includes(parseInt(newRole)) && parseInt(newRole) !== currentRole) {
        if (confirm('Bạn có chắc muốn thay đổi vai trò của tài khoản này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'change_role';
            form.appendChild(actionInput);

            const accountIdInput = document.createElement('input');
            accountIdInput.type = 'hidden';
            accountIdInput.name = 'account_id';
            accountIdInput.value = accountId;
            form.appendChild(accountIdInput);

            const newRoleInput = document.createElement('input');
            newRoleInput.type = 'hidden';
            newRoleInput.name = 'new_role';
            newRoleInput.value = newRole;
            form.appendChild(newRoleInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
}

function toggleLock(accountId, currentStatus) {
    const action = currentStatus === 'active' ? 'lock' : 'unlock';
    const message = action === 'lock' ? 'Bạn có chắc muốn khóa tài khoản này?' : 'Bạn có chắc muốn mở khóa tài khoản này?';

    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);

        const accountIdInput = document.createElement('input');
        accountIdInput.type = 'hidden';
        accountIdInput.name = 'account_id';
        accountIdInput.value = accountId;
        form.appendChild(accountIdInput);

        document.body.appendChild(form);
        form.submit();
    }
}