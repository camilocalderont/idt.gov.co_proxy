AOS.init();

function isMobile() {
  return /Mobi|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}


/* off canvas menu mobile and search*/

const searchIcon = document.getElementById("search-icon");
const menuIcon = document.getElementById("menu-icon");
const dialogMenuSearch = document.getElementById("of__mms");
const closeMenuIcons = document.querySelectorAll(".header__close-menu-off-canvas");


var slideMMS = new Swiper(".slide__of__mms", {
    spaceBetween: 30,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".slide__of__mms__next",
        prevEl: ".slide__of__mms__prev",
    },
});

if(searchIcon){
    searchIcon.addEventListener("click", ()=>{
        dialogMenuSearch.showModal();
        document.body.classList.add('no-scroll'); // Bloquea

    })
}

if(menuIcon){
    menuIcon.addEventListener("click", ()=>{
        dialogMenuSearch.showModal();
        setTimeout(() => {
            slideMMS.slideTo(1, 0);
            slideMMS.update();
        }, 100);
        document.body.classList.add('no-scroll'); // Bloquea
    })
}

if(closeMenuIcons){
    closeMenuIcons.forEach((closeIcon) => {
        closeIcon.addEventListener("click", ()=>{
            dialogMenuSearch.close();
            document.body.classList.remove('no-scroll'); // Desbloquea
        });
    });
}

if(!isMobile()){
    slideMMS.allowTouchMove = false;
    slideMMS.slideTo(0);             
    slideMMS.allowSlideNext = false;
    slideMMS.allowSlidePrev = false;
}







/* Video banner*/

const videoBanner = document.getElementById("idt-banner__video");

if(videoBanner){
    const videoBtnSound = document.getElementById("idt-banner__btn-sound");
    videoBtnSound.addEventListener("click", ()=>{
        if (videoBanner.muted) {
            videoBanner.muted = false; 
            videoBtnSound.textContent = "Desactivar Sonido";
            videoBtnSound.style.backgroundColor = "#ffffff80";
        }
        else {
            videoBanner.muted = true;
            videoBtnSound.textContent = "Activar Sonido";
            videoBtnSound.style.backgroundColor = "#fff";
        }
    });
      
}

/* Acordeon */

let acordeon = document.querySelector('.accordion-normativa');

if(acordeon){
    var accordeonNormativa = new Accordion(".accordion-normativa", {
      duration: 300,
      showMultiple: true,
    });
}

acordeon = document.querySelector('.accordion__menu-mobile');
if(acordeon){
    var accordeonMenuMobile = new Accordion(".accordion__menu-mobile", {
      duration: 300,
    });
}

acordeon = document.querySelector('.accordion-block');

if(acordeon){
    var accordeonBlock = new Accordion(".accordion-block", {
      duration: 300,
      showMultiple: true,
    });
}



