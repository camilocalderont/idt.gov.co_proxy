const slideLocations = document.querySelector(".mySwiper--localidades");
var sBrowser,
  sUsrAg = navigator.userAgent;

if (sUsrAg.indexOf("Chrome") > -1) {
  sBrowser = "Google Chrome";
} else if (sUsrAg.indexOf("Safari") > -1) {
  sBrowser = "Apple Safari";
} else if (sUsrAg.indexOf("Opera") > -1) {
  sBrowser = "Opera";
} else if (sUsrAg.indexOf("Firefox") > -1) {
  sBrowser = "Mozilla Firefox";
} else if (sUsrAg.indexOf("MSIE") > -1) {
  sBrowser = "Microsoft Internet Explorer";
}

function activeMap(){
    document.querySelector(".active-map").classList.remove("active-map");
    setTimeout(()=>{
      	const hash = location.hash;
      	if(hash === ""){
      	    document.getElementById("usaquen-map").classList.add("active-map");
      	}else{
            document.getElementById(hash.slice(1) + "-map").classList.add("active-map");
  	    }
    }, 50);
}

activeMap();

var swiper = new Swiper(".mySwiper--localidades", {
  spaceBetween: 30,
  hashNavigation: {
    watchState: true,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
});
     
if ( sBrowser == "Apple Safari" || sBrowser == "Mozilla Firefox" ) {
    const mapList = document.querySelectorAll("#bogota-map a");
    const nextArrow = document.querySelector(".mySwiper--localidades .swiper-button-next");
    const prevArrow = document.querySelector(".mySwiper--localidades .swiper-button-prev");
    
    for(let i = 0; i < mapList.length; i++){
        mapList[i].addEventListener("click", ()=>{
            activeMap();
        })
    }
    
    nextArrow.addEventListener("click", ()=>{
        setTimeout(()=>{
            activeMap();    
        }, 300)
    });
    
    prevArrow.addEventListener("click", ()=>{
        setTimeout(()=>{
            activeMap();    
        }, 300)
    });
    
}else{
    navigation.addEventListener('navigate', () => {
        activeMap();
    });
}
