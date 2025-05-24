import React from "react";
import "./ProductDetail.css";
const ProductDetail = () => {
  return (
    <div className="main">
      <div className="flex flex-row justify-evenly p-6">
        <section className="left-product-details">
          <img src="" alt="" />
          <div className="mt-6 flex gap-2">
            <button className="flex-1 border border-red-500 text-red-500 py-2 rounded-lg flex items-center justify-center gap-2">
              🛒 Thêm vào giỏ hàng
            </button>
            <button className="flex-1 bg-red-500 text-white py-2 rounded-lg">
              Mua ngay
            </button>
          </div>

          <div className="mt-6">
            <h3 className="font-bold text-gray-900">
              Chính sách ưu đãi của Fahasa
            </h3>
            <ul className="mt-2 space-y-2 text-sm text-gray-700">
              <li className="flex items-center gap-2">
                🚚 <strong>Thời gian giao hàng:</strong> Giao nhanh và uy tín
              </li>
              <li className="flex items-center gap-2">
                🔄 <strong>Chính sách đổi trả:</strong> Đổi trả miễn phí toàn
                quốc
              </li>
              <li className="flex items-center gap-2">
                🏬 <strong>Chính sách khách sỉ:</strong> Ưu đãi khi mua số lượng
                lớn
              </li>
            </ul>
          </div>
        </section>
        <div className="gap-4 flex flex-col max-w-[60%]">
          <section className="right-product-details">
            <h1>Tây du kí</h1>
            <div className="mt-2 text-sm text-gray-700">
              <div className="flex -flex-row justify-between mr-10">
                <div className="flex flex-col gap-2.5">
                  <p>
                    <span className="font-medium">Nhà cung cấp:</span>{" "}
                    <span className="text-blue-600">NXB Trẻ</span>
                  </p>
                  <p>
                    <span className="font-medium">Nhà xuất bản:</span>{" "}
                    <span className="font-bold">Trẻ</span>
                  </p>
                </div>
                <div className="flex flex-col gap-2.5">
                  <p>
                    <span className="font-medium">Tác giả:</span>{" "}
                    <span className="font-bold">Hajime Isayama</span>
                  </p>
                  <p>
                    <span className="font-medium">Độ tuổi:</span>{" "}
                    <span className="font-bold">18+</span>
                  </p>
                </div>
              </div>
            </div>
            <div className="flex flex-row gap-2.5 items-center">
              <p className="text-[#c92127] font-bold text-[32px]">50.000đ</p>
              <p className="old-price line-through">70.000đ</p>
              <div className="discount-percent">-15%</div>
            </div>
          </section>
          <section>
            <h2>Thông tin chi tiết</h2>
            <div className="mt-2 text-sm text-gray-700 max-w-[500px]">
              <ProductDetailLabel label={"Mã hàng:"} value={"8934974205753"} />
              <ProductDetailLabel label={"Độ tuổi:"} value={"18+"} />
              <ProductDetailLabel label={"Nhà cung cấp:"} value={"NXB Trẻ"} />
              <ProductDetailLabel label={"Tác giả:"} value={"Hajime Isayama"} />
              <ProductDetailLabel label={"Người Dịch:"} value={"Thế Đăng"} />
              <ProductDetailLabel label={"Năm XB:"} value={"2024"} />
              <ProductDetailLabel label={"Ngôn Ngữ:"} value={"Tiếng Việt"} />
              <ProductDetailLabel label={"Trọng lượng gr:"} value={"223"} />
              <ProductDetailLabel
                label={"Kích Thước Bao Bì:"}
                value={"19 x 13 x 0.9 cm"}
              />
              <ProductDetailLabel label={"Số trang:"} value={"192"} />
              <ProductDetailLabel label={"Hình thức"} value={"Bìa Mềm"} />
            </div>
          </section>
          <section>
            <h3>Mô tả sản phẩm</h3>
            <p>
              ONE OF THE BEST BOOKS OF THE YEAR "The New York Times" "The
              Washington Post" "The Wall Street Journal" NPR "Vanity Fair"
              "Vogue" "Minneapolis Star Tribune" "St. Louis Post-Dispatch" "The
              Guardian " "O, The Oprah Magazine" Slate "Newsday" Buzzfeed "The
              Economist" " Newsweek" "People" " Kansas City Star" Shelf
              Awareness "Time Out New York " " Huffington Post" Book Riot
              Refinery29 "Bookpage" "Publishers Weekly Kirkus" WINNER OF THE
              KIRKUS PRIZE A MAN BOOKER PRIZE FINALIST A NATIONAL BOOK AWARD
              FINALIST "A Little Life" follows four college classmates broke,
              adrift, and buoyed only by their friendship and ambition as they
              move to New York in search of fame and fortune. While their
              relationships, which are tinged by addiction, success, and pride,
              deepen over the decades, the men are held together by their
              devotion to the brilliant, enigmatic Jude, a man scarred by an
              unspeakable childhood trauma. A hymn to brotherly bonds and a
              masterful depiction of love in the twenty-first century, Hanya
              Yanagihara s stunning novel is about the families we are born
              into, and those that we make for ourselves."
            </p>
          </section>
        </div>
      </div>
    </div>
  );
};

export default ProductDetail;

const ProductDetailLabel = ({
  label,
  value,
}: {
  label: string;
  value: string;
}) => {
  return (
    <div className="border-gray-200 pb-2 my-4 border-b">
      <p className="flex-row flex justify-between">
        <span className="font-medium text-gray-600">{label}</span>{" "}
        <span className="">{value}</span>
      </p>
    </div>
  );
};
