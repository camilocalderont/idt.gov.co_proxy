var swiper = new Swiper(".mySwiper--pits", {
  slidesPerView: 1,
  spaceBetween: 30,
  breakpoints: {
      768:{
          slidesPerView: 3,
      }
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
});
