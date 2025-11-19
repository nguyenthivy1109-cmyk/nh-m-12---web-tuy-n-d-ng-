<!-- Categories Table -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-tags"></i> Danh sách nhóm ngành nghề</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus"></i> Thêm nhóm ngành nghề
        </button>
    </div>

    <div class="table-responsive">
        <table id="categoriesTable" class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Tên nhóm ngành nghề</th>
                    <th>Slug</th>
                    <th>Số tin tuyển dụng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['nhom_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($category['ten_nhom']); ?></strong>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($category['slug']); ?></code>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo number_format($category['job_count']); ?> tin
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning action-btn" onclick="editCategory(<?php echo $category['nhom_id']; ?>, '<?php echo addslashes($category['ten_nhom']); ?>', '<?php echo addslashes($category['slug']); ?>')" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger action-btn" onclick="deleteCategory(<?php echo $category['nhom_id']; ?>, '<?php echo addslashes($category['ten_nhom']); ?>')" title="Xóa" <?php echo $category['job_count'] > 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Thêm nhóm ngành nghề</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_category">

                    <div class="mb-3">
                        <label class="form-label">Tên nhóm ngành nghề <span class="text-danger">*</span></label>
                        <input type="text" name="ten_nhom" class="form-control" required
                               placeholder="VD: Công nghệ thông tin, Kế toán, Marketing...">
                        <small class="text-muted">Tên hiển thị của nhóm ngành nghề</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" id="addSlug"
                               placeholder="VD: cong-nghe-thong-tin, ke-toan, marketing...">
                        <small class="text-muted">Slug dùng để phân loại và tìm kiếm. Để trống để tự động tạo.</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Lưu ý:</strong> Slug phải là duy nhất và chỉ chứa chữ cái, số, dấu gạch ngang.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Thêm mới
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Chỉnh sửa nhóm ngành nghề</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="nhom_id" id="editNhomId">

                    <div class="mb-3">
                        <label class="form-label">Tên nhóm ngành nghề <span class="text-danger">*</span></label>
                        <input type="text" name="ten_nhom" id="editTenNhom" class="form-control" required>
                        <small class="text-muted">Tên hiển thị của nhóm ngành nghề</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="editSlug" class="form-control">
                        <small class="text-muted">Slug dùng để phân loại và tìm kiếm. Để trống để tự động tạo.</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Lưu ý:</strong> Slug phải là duy nhất và chỉ chứa chữ cái, số, dấu gạch ngang.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trash"></i> Xóa nhóm ngành nghề</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="nhom_id" id="deleteNhomId">

                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Xác nhận xóa:</strong> <span id="deleteCategoryName"></span>
                    </div>

                    <p class="mb-0">Bạn có chắc chắn muốn xóa nhóm ngành nghề này không?</p>
                    <small class="text-muted">Hành động này không thể hoàn tác.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#categoriesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        order: [[1, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });

    // Auto-generate slug when typing category name
    $('#addCategoryModal input[name="ten_nhom"]').on('input', function() {
        var name = $(this).val();
        var slug = generateSlug(name);
        $('#addSlug').val(slug);
    });

    $('#editTenNhom').on('input', function() {
        var name = $(this).val();
        var slug = generateSlug(name);
        $('#editSlug').val(slug);
    });
});

function generateSlug(text) {
    return text
        .toLowerCase()
        .replace(/à|á|ả|ã|ạ|ă|ằ|ắ|ẳ|ẵ|ặ|â|ầ|ấ|ẩ|ẫ|ậ/g, 'a')
        .replace(/đ/g, 'd')
        .replace(/è|é|ẻ|ẽ|ẹ|ê|ề|ế|ể|ễ|ệ/g, 'e')
        .replace(/ì|í|ỉ|ĩ|ị/g, 'i')
        .replace(/ò|ó|ỏ|õ|ọ|ô|ồ|ố|ổ|ỗ|ộ|ơ|ờ|ớ|ở|ỡ|ợ/g, 'o')
        .replace(/ù|ú|ủ|ũ|ụ|ư|ừ|ứ|ử|ữ|ự/g, 'u')
        .replace(/ỳ|ý|ỷ|ỹ|ỵ/g, 'y')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
}

function editCategory(id, name, slug) {
    $('#editNhomId').val(id);
    $('#editTenNhom').val(name);
    $('#editSlug').val(slug);
    $('#editCategoryModal').modal('show');
}

function deleteCategory(id, name) {
    $('#deleteNhomId').val(id);
    $('#deleteCategoryName').text(name);
    $('#deleteCategoryModal').modal('show');
}
</script>
