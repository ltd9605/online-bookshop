function deleteProduct(id) {
    Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Không thể hoàn tác hành động này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    'delete-product': true,
                    id: id
                },
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        Swal.fire(
                            'Đã xóa!',
                            'Phiếu nhập đã được xóa.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Lỗi!',
                            'Có lỗi xảy ra khi xóa.',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'Lỗi!',
                        'Có lỗi xảy ra khi kết nối đến server.',
                        'error'
                    );
                }
            });
        }
    });
}


//=================chi tieets========================

$(window).on("click", function (event) {
    if ($(event.target).is(".ctpn-container")) {
        $(".ctpn-container").css("display", "none");
    }
});


// $(document).ready(function () {
//     $('a.view').click(function (e) {
//         e.preventDefault();
//         const idPhieuNhap = $(this).data('id');
//         // alert(idPhieuNhap);
//         loadChiTietPhieuNhap(idPhieuNhap);
//     });
// });

// function loadChiTietPhieuNhap(idPhieuNhap) {
//     $.ajax({
//         url: './get_chitietphieunhap.php',
//         type: 'POST',
//         dataType: 'json',
//         data: { id: idPhieuNhap },
//         success: function (response) {
//             if (response.success) {
//                 // Hiển thị thông tin tổng quan
//                 $('#receiver-name').text(response.summary.ten_nguoi_nhap);
//                 $('#total-amount').text(formatCurrency(response.summary.tongtien) + 'đ');
//                 $('#order-date').text(response.summary.date);

//                 // Hiển thị danh sách sản phẩm
//                 let html = '';
//                 response.details.forEach(item => {
//                     html += `
//                         <tr>
//                             <td>${item.ten_sach || 'N/A'}</td>
//                             <td>${item.ten_ncc || 'N/A'}</td>
//                             <td>${formatCurrency(item.gianhap)}đ</td>
//                             <td>${item.soluong}</td>
//                             <td>${formatCurrency(item.gianhap * item.soluong)}đ</td>
//                         </tr>
//                     `;
//                 });
//                 $('.ctpn-container table tbody').html(html);

//                 // Hiển thị modal
//                 $('.ctpn-container').show();
//             } else {
//                 Swal.fire('Lỗi!', response.message, 'error');
//             }
//         },
//         error: function () {
//             Swal.fire('Lỗi!', 'Không thể tải chi tiết phiếu nhập', 'error');
//         }
//     });
// }

// {
//     function formatCurrency(amount) {
//         return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
//     }
// }
