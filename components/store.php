  <div id="menuContent" class="menuContent hidden absolute top-full left-0  bg-white shadow-lg z-50">
    <div class="sideBarMenu ">
      <?php
      for ($i = 6; $i < 13; $i++) {
        ?>
        <div class="tablinks " data-id="<?php echo $i; ?>">
          Lớp <?php echo $i ?>
        </div>
        <?php
      }
      ?>
      <script>
        document.querySelectorAll(".tablinks").forEach(tab => {
          tab.addEventListener("mouseenter", function () {
            let Class = this.dataset.id;
            openTab(this, Class);
          })
        })
        function openTab(tab, Class) {

          const Tablinks = document.querySelectorAll(".tablinks");
          for (let i = 0; i < Tablinks.length; i++) {
            Tablinks[i].className = Tablinks[i].className.replace(" onTab", "");
          }
          tab.classList.add("onTab");
        }
      </script>
    </div>

    <div style="width: 100%;">
      <div>
        <div>
          <img src="/LTW-UD2/images/forHeader/menuBook.png" alt="">
        </div>
        SÁCH TRONG NƯỚC
      </div>
      <div class="detailMenu">
        <!-- div*3 -->
      </div>
      <script>
        const detailMenu = document.querySelector(".detailMenu");
        const tablinks = document.querySelectorAll(".tablinks");
        tablinks.forEach(tab => {
          tab.addEventListener("mouseenter", function () {
            const Class = this.dataset.id;
            fetch(`contentMenu.php/?Class=${Class}`).
              then(response => response.text()).
              then(data => {
                detailMenu.innerHTML = data;
              })
          })
        })
      </script>
    </div>
  </div>