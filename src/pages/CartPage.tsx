import React, { useState } from "react";
import "./CartPage.css";
import CtaButton from "../../public/components/CtaButton";
const CartPage = () => {
  const [selectAll, setSelectAll] = useState(false);
  const [selectedItems, setSelectedItems] = useState([true]); // Initially, select the first item
  const [quantity, setQuantity] = useState(2);

  const handleSelectAll = () => {
    setSelectAll(!selectAll);
    setSelectedItems(Array(1).fill(!selectAll)); // Update selected items based on selectAll
  };

  const handleSelectItem = (index) => {
    const newSelectedItems = [...selectedItems];
    newSelectedItems[index] = !newSelectedItems[index];
    setSelectedItems(newSelectedItems);
  };

  const handleQuantityChange = (value) => {
    setQuantity(value);
  };

  return (
    <div className="min-h-[100vh]">
      <div className="container mx-auto gap-4 p-4 flex flex-row">
        <section className="min-w-[70%]">
          {/* Header */}
          <div className="flex items-center mb-4">
            <div className=" w-[50%] checkbox-wrapper-13">
              <input
                type="checkbox"
                // checked={selectedItems[0]}
                // onChange={() => handleSelectItem(0)}

                className="mr-2"
                id="select-all"
              />
              <label htmlFor="select-all">Chọn tất cả (1 sản phẩm)</label>
            </div>
            <div className="w-[25%]">Số lượng</div>
            <div className="w-[25%]">Thành tiền</div>
          </div>
          <CartItemDisplay
            image={
              "https://skyryedesign.com/wp-content/uploads/2016/04/56c6f9b7efad5-cover-books-design-illustrations.jpg"
            }
          />
        </section>
        <section className="min-w-[30%]">
          {/* Totals */}
          <div className="p-4 mb-4">
            <div className="flex justify-between mb-2">
              <div>Thành tiền</div>
              <div>0₫</div> {/* Replace with dynamic value */}
            </div>
            <div className="flex justify-between font-bold">
              <div>Tổng Số Tiền (gồm VAT)</div>
              <div className="text-[red] font-bold text-2xl">0₫</div>{" "}
              {/* Replace with dynamic value */}
            </div>
          </div>

          {/* Checkout Button */}
          <button className="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 rounded">
            THANH TOÁN
          </button>
        </section>
      </div>
    </div>
  );
};

const EmptyCart = () => {
  return (
    <div className="background">
      <div className="empty-cart">
        <img className="h-48" src="/empty-cart.png" alt="" />
        <p>Chưa có sản phẩm trog giỏ hàng của bạn.</p>
        <CtaButton text={"Mua Sắm Ngay"} />
      </div>
    </div>
  );
};

export default CartPage;

const CartItemDisplay = ({ image }: { image: string }) => {
  return (
    <>
      <div className="p-4 mb-4">
        <div className="flex items-center mb-4">
          <div className="w-[50%] flex flex-row items-center">
            <div className="checkbox-wrapper-13">
              <input
                type="checkbox"
                // checked={selectedItems[0]}
                // onChange={() => handleSelectItem(0)}
                id="c1-13"
                className="mr-2"
              />
            </div>
            <img
              src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTSla9IRyGU1tyIzD20TvcVXjn5goYW2z0MPA&s"
              alt="Product"
              className="w-20 h-20 min-h-[100px] object-cover rounded mr-4"
            />{" "}
            {/* Replace with your image URL */}
            <div className="h-[100%] gap-10 flex flex-col">
              <div className="font-medium">A Little Life</div>

              <div className="flex flex-row gap-2.5">
                <div className="font-bold">289.850₫</div>
                <div className="text-gray-500 line-through text-sm">
                  341.000₫
                </div>
              </div>
            </div>
          </div>
          <div className="w-[25%]">
            <div className="flex items-center border rounded-xl px-1 h-8 border-gray-300  w-max">
              <button
                onClick={
                  () => {}
                  // handleQuantityChange(Math.max(1, quantity - 1))
                }
                className="px-2 py-1 font-bold mt-[-23px] text-2xl text-gray-500"
              >
                _
              </button>
              <span className="mx-2">{2}</span>
              <button
                // onClick={() => handleQuantityChange(quantity + 1)}
                className="px-2 py-1  text-2xl text-gray-500"
              >
                +
              </button>
            </div>
          </div>
          <div className="px-4 text-[#FB2C36] font-bold text-xl w-[25%]">
            <p>86.400 đ</p>
            <i className="fa-solid fa-trash"></i>
          </div>
        </div>
      </div>
    </>
  );
};
