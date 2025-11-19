<!-- Skills Dictionary Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-cogs"></i> Danh sách Từ điển kỹ năng</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
            <i class="fas fa-plus"></i> Thêm kỹ năng mới
        </button>
    </div>

    <div class="table-responsive">
        <table id="skillsTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên kỹ năng</th>
                    <th>Slug</th>
                    <th>Sử dụng trong CV</th>
                    <th>Sử dụng trong tin đăng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($skills as $skill): ?>
                <tr>
                    <td><?php echo htmlspecialchars($skill['kn_id']); ?></td>
                    <td><?php echo htmlspecialchars($skill['ten_kn']); ?></td>
                    <td><?php echo htmlspecialchars($skill['slug']); ?></td>
                    <td>
                        <span class="badge bg-info">
                            <i class="fas fa-file-alt"></i> <?php echo number_format($skill['cv_count']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-warning">
                            <i class="fas fa-briefcase"></i> <?php echo number_format($skill['job_count']); ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary action-btn"
                                onclick="editSkill(<?php echo $skill['kn_id']; ?>, '<?php echo addslashes($skill['ten_kn']); ?>', '<?php echo addslashes($skill['slug']); ?>')">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger action-btn"
                                onclick="deleteSkill(<?php echo $skill['kn_id']; ?>, '<?php echo addslashes($skill['ten_kn']); ?>')">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Skill Modal -->
<div class="modal fade" id="addSkillModal" tabindex="-1" aria-labelledby="addSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSkillModalLabel"><i class="fas fa-plus"></i> Thêm kỹ năng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_skill">

                    <div class="mb-3">
                        <label for="add_ten_kn" class="form-label">Tên kỹ năng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_ten_kn" name="ten_kn" required
                               placeholder="Nhập tên kỹ năng">
                        <div class="form-text">Ví dụ: PHP, JavaScript, Python, v.v.</div>
                    </div>

                    <div class="mb-3">
                        <label for="add_slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="add_slug" name="slug"
                               placeholder="Để trống để tự động tạo từ tên kỹ năng">
                        <div class="form-text">Slug sẽ được sử dụng trong URL và tìm kiếm</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm kỹ năng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Skill Modal -->
<div class="modal fade" id="editSkillModal" tabindex="-1" aria-labelledby="editSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSkillModalLabel"><i class="fas fa-edit"></i> Sửa kỹ năng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_skill">
                    <input type="hidden" name="kn_id" id="edit_kn_id">

                    <div class="mb-3">
                        <label for="edit_ten_kn" class="form-label">Tên kỹ năng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_kn" name="ten_kn" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="edit_slug" name="slug">
                        <div class="form-text">Slug sẽ được sử dụng trong URL và tìm kiếm</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Skill Modal -->
<div class="modal fade" id="deleteSkillModal" tabindex="-1" aria-labelledby="deleteSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSkillModalLabel"><i class="fas fa-trash"></i> Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa kỹ năng <strong id="delete_skill_name"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Cảnh báo:</strong> Việc xóa kỹ năng có thể ảnh hưởng đến các CV và tin tuyển dụng đang sử dụng kỹ năng này.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_skill">
                    <input type="hidden" name="kn_id" id="delete_kn_id">
                    <button type="submit" class="btn btn-danger">Xóa kỹ năng</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#skillsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: 5 }
        ]
    });
});

// Auto-generate slug from skill name
$('#add_ten_kn').on('input', function() {
    var name = $(this).val();
    var slug = generateSlug(name);
    $('#add_slug').val(slug);
});

$('#edit_ten_kn').on('input', function() {
    var name = $(this).val();
    var slug = generateSlug(name);
    $('#edit_slug').val(slug);
});

// Function to generate slug
function generateSlug(text) {
    return text
        .toLowerCase()
        .replace(/[àáảãạăằắẳẵặâầấẩẫậ]/g, 'a')
        .replace(/[èéẻẽẹêềếểễệ]/g, 'e')
        .replace(/[ìíỉĩị]/g, 'i')
        .replace(/[òóỏõọôồốổỗộơờớởỡợ]/g, 'o')
        .replace(/[ùúủũụưừứửữự]/g, 'u')
        .replace(/[ỳýỷỹỵ]/g, 'y')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .trim()
        .replace(/^-+|-+$/g, '');
}

// Edit skill function
function editSkill(kn_id, ten_kn, slug) {
    $('#edit_kn_id').val(kn_id);
    $('#edit_ten_kn').val(ten_kn);
    $('#edit_slug').val(slug);
    $('#editSkillModal').modal('show');
}

// Delete skill function
function deleteSkill(kn_id, ten_kn) {
    $('#delete_kn_id').val(kn_id);
    $('#delete_skill_name').text(ten_kn);
    $('#deleteSkillModal').modal('show');
}
</script>