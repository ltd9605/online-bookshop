const incrementBtn = document.querySelector('.increment')
const decrementBtn = document.querySelector(".decrement")
const counter = document.querySelector(".counter")
let num = counter.innerHTML

incrementBtn.addEventListener("click",()=>{
    num++
    counter.innerHTML = num
    
})

decrementBtn.addEventListener("click",()=>{
    if (num>0) {
        num --
        counter.innerHTML = num
    }
    
})

