const map = document.getElementById("pit__mapa");
const closeMap = document.getElementById("pit__mapa__close");
const openMap = document.getElementById("pit__mapa__open");

closeMap.addEventListener("click", ()=>{
    map.close();
});

openMap.addEventListener("click", ()=>{
    map.show();
});

