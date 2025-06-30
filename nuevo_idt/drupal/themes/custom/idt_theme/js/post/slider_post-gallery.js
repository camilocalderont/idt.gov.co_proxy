const sliderPostGallery = new Swiper('.slider-post-gallery', {
  // Optional parameters
  slidesPerView: 1,
  spaceBetween: 16,
  
  breakpoints: {
      600: {
          slidesPerView: 2,
      },
  },

  // If we need pagination
  pagination: {
    el: '.swiper-pagination',
    dynamicBullets: true,
  },

  // Navigation arrows
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },

});