<footer class="bg-gray-100 text-gray-700 pt-12 w-full mt-4">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

      <!-- Logo & Description -->
      <div class="space-y-4">
        <img src="/LTW-UD2/images/login/image1.jpg" alt="Logo" class="w-20">
        <img src="/LTW-UD2/images/login/logo-bo-cong-thuong-da-thong-bao1.webp" alt="Đã thông báo" class="w-28 mt-4">
        <div class="flex gap-3 text-2xl text-gray-500 mt-4">
          <i class='bx bxl-facebook-circle hover:text-blue-600'></i>
          <i class='bx bxl-instagram-alt hover:text-pink-500'></i>
          <i class='bx bxl-youtube hover:text-red-600'></i>
          <i class='bx bxl-twitter hover:text-blue-400'></i>
        </div>
      </div>


      <div>
        <h4 class="font-bold text-lg mb-4">LẬP TRÌNH VIÊN</h4>
        <ul class="space-y-2 italic text-sm">
          <li class="footer-item">Huỳnh Tấn Bảo</li>
          <li class="footer-item">Lâm Thái Yến Nhi</li>
          <li class="footer-item">Phan Phước Hiền</li>
          <li class="footer-item">Lê Tiến Đức</li>
          <li class="footer-item">Nguyễn Gia Huy</li>
        </ul>
      </div>


      <div class="w-full h-64 rounded-lg overflow-hidden">
      <iframe 
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.417458588549!2d106.6842107!3d10.7793043!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f2f38563351%3A0xe2afc7d527483b0e!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBTw6BpIEfDsm4gLSBDxqEgU-G7nyAx!5e0!3m2!1svi!2s!4v1746727545820!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

      </div>

      <!-- Liên hệ -->
      <div>
        <h4 class="font-bold text-lg mb-4">LIÊN HỆ</h4>
        <ul class="space-y-2 text-sm">
          <li class="flex items-center gap-2">Cơ sở chính: 273 An Dương Vương – Phường 3 – Quận 5 </li>
          <li class="flex items-center gap-2">Cơ sở 1: 105 Bà Huyện Thanh Quan – Phường Võ Thị Sáu – Quận 3</li>
          <li class="flex items-center gap-2">Cơ sở 2: 04 Tôn Đức Thắng – Phường Bến Nghé – Quận 1</li>
        </ul>

      </div>
    </div>

    <!-- Payments -->
    <div class="mt-12 border-t pt-6 flex flex-wrap justify-center items-center gap-6">
      <img src="/LTW-UD2/images/login/logo_lex.webp" class="h-10">
      <img src="/LTW-UD2/images/login/Logo_ninjavan.webp" class="h-10">
      <img src="/LTW-UD2/images/login/vnpost1.webp" class="h-10">
      <img src="/LTW-UD2/images/login/vnpay_logo.webp" class="h-10">
      <img src="/LTW-UD2/images/login/shopeepay_logo.webp" class="h-10">
      <img src="/LTW-UD2/images/login/momopay.webp" class="h-10 w-auto">
    </div>

    <p class="text-center text-sm text-gray-400 mt-6 pb-6">© 2025 BookStore. All rights reserved.</p>
  </div>

  <style>
    .footer-item {
      position: relative;
      padding-left: 15px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease-in-out;
    }

    .footer-item::before {
      content: "—";
      position: absolute;
      left: -20px;
      color: #ec4899;
      opacity: 0;
      transition: all 0.3s ease-in-out;
    }

    .footer-item:hover {
      transform: translateX(6px);
      color: #ec4899;
    }

    .footer-item:hover::before {
      left: -10px;
      opacity: 1;
    }
  </style>
</footer>
