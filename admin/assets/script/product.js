$(document).ready(function () {
    $('#searchInput').on('keyup change', function () {
        let keyword = $(this).val();

        $.ajax({
            type: 'POST',
            url: './thongtinsanpham.php',
            data: {
                valueSearch: keyword
            },
            success: function (response) {
                $('.table-container').html(response);
            },
            error: function (xhr, status, error) {
                console.error("AJAX lỗi:", error);
                console.log("Status:", status);
                console.log("Response:", xhr.responseText);
            }
        });
    });
});

function loadProducts(page = 1, search = "") {
    $.ajax({
        type: 'POST',
        url: './thongtinsanpham.php',
        data: {
            valueSearch: search,
            currentPage: page
        },
        success: function (response) {
            $('.table-container').html(response);
        },
        error: function (xhr, status, error) {
            console.error("AJAX lỗi:", error);
        }
    });
}

$(document).on('keyup change', '#searchInput', function () {
    const keyword = $(this).val();
    loadProducts(1, keyword);
});

$(document).on('click', '.page-number', function (e) {
    e.preventDefault();
    const page = $(this).data('page');
    const search = $('#searchInput').val();
    loadProducts(page, search);
});

$(document).on('click', '.delete-icon', function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "Bạn muốn xóa sản phẩm?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Xóa",
        cancelButtonText: "Hủy bỏ",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Đã xóa sản phẩm",
                text: "",
                icon: "success",
            });
            $.ajax({
                type: "POST",
                url: "./thongtinsanpham.php",
                data: {
                    id: id,
                    "delete-product": true,
                },
                dataType: "html",
                success: function (response) {
                    console.log(response);
                    $.ajax({
                        type: "POST",
                        url: "./sanphan.php",
                        dataType: "html",
                        success: function (response) {
                            $('.sp-concon').html(response);
                            location.reload();
                        },
                    });

                },
            });
        }
    });
});

$(document).on('click', '.check-icon', function () {
    const icon = $(this);
    const id = icon.data('id');
    const isCurrentlyActive = icon.hasClass('fa-toggle-on');
    const newStatus = isCurrentlyActive ? 0 : 1;

    const title = newStatus === 1 ? "Bạn muốn cho phép bán sản phẩm này?" : "Bạn muốn ngừng bán sản phẩm này?";

    Swal.fire({
        title: title,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Xác nhận",
        cancelButtonText: "Hủy",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "./thongtinsanpham.php",
                data: {
                    id: id,
                    isActive: newStatus,
                    "update-status": true
                },
                dataType: "json",
                success: function (response) {
                    if (response && response.success) {
                        // Cập nhật trực tiếp icon mà không cần load lại trang
                        if (newStatus === 1) {
                            icon.removeClass('fa-toggle-off').addClass('fa-toggle-on');
                            icon.css('color', 'green');
                            icon.attr('title', 'Đang bán');
                        } else {
                            icon.removeClass('fa-toggle-on').addClass('fa-toggle-off');
                            icon.css('color', 'red');
                            icon.attr('title', 'Ngừng bán');
                        }

                        Swal.fire("Thành công", response.message, "success");
                    } else {
                        const errorMsg = response && response.message ? response.message : "Không thể cập nhật trạng thái!";
                        Swal.fire("Lỗi", errorMsg, "error");
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire("Lỗi", "Lỗi kết nối đến server: " + error, "error");
                }
            });
        }
    });
});
//===================================================================================



$(document).on("click", ".update-icon", function (e) {
    e.preventDefault();
    var id = $(this).data("id");
    console.log(id);
    $.ajax({
        type: "POST",
        url: "./modalsp.php",
        data: {
            id: id,
            "update-product": true,
        },
        dataType: "html",
        success: function (response) {
            $(".modal-content").html(response);
            $("#myModal").css("display", "flex");

            // $('#book-name').data('original', $('#book-name').val());
            // $('#subject').data('original', $('#subject').val());
            // $('#class').data('original', $('#class').val());
            // $('#image-url').data('original', $('#image-url').val());
            // $('#description').data('original', $('#description').val());
        },
        error: function () {
            alert("Có lỗi xảy ra khi lấy dữ liệu sản phẩm.");
        },
    });
});
$(window).on("click", function (event) {
    if ($(event.target).is("#myModal")) {
        $("#myModal").css("display", "none");
    }
});


$(document).on("change", "#book-name", function () {
    const $input = $(this);
    const value = $input.val().trim();
    const original = $input[0].defaultValue;

    if (value === "") {
        Swal.fire({
            icon: "error",
            title: "Lỗi nhập liệu",
            text: "Tên sách không được để trống!",
        });
        $input.val(original);
    }
});


$(document).on("change", "#description", function () {
    const $textarea = $(this);
    const value = $textarea.val().trim();
    const original = $textarea.data("original");

    if (value === "") {
        Swal.fire({
            icon: "error",
            title: "Lỗi nhập liệu",
            text: "Mô tả không được để trống!",
        });

        $textarea.val(original);
    }
});





































