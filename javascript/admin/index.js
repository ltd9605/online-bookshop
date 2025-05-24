import { AdminTabNavigation } from "./adminTabNavigation.js";
import { adminLoginPopup } from "./adminLoginPopup.js";

AdminTabNavigation();
adminLoginPopup();

document.addEventListener("DOMContentLoaded", () => {
    // import { AdminTabNavigation } from "./adminTabNavigation.js";
    // import { adminLoginPopup } from "./adminLoginPopup.js";

    AdminTabNavigation();
    adminLoginPopup();

    const Btn = document.querySelectorAll(".admin-nav-btn");
    const views = document.querySelectorAll("div.content");

    console.log(Btn);
    console.log(views);

        // views.forEach((view) => {
        //     view.style.display = "none";
        // });
        // views.forEach((view) => {
        //     if (view.getAttribute("page") === "analytics") {
        //         view.style.display = "block";
        //     }
        // });
        // Btn.forEach((btn) => {
        //     btn.addEventListener("click", (e) => {
        //         console.log(e.target.getAttribute("page"));
        //         views.forEach((view) => {
        //             view.style.display = "none";
        //         });
        //         views.forEach((view) => {
        //             if (view.getAttribute("page") === e.target.getAttribute("page")) {
        //                 view.style.display = "block";
        //             }
        //         });
        //     });
        // });
});