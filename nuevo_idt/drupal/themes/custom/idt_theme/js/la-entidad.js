const listItems = document.querySelectorAll(".idt-list__item");
const listItemsContent = document.querySelectorAll(".idt-list__item > ul");

if(listItems){
    for (let i = 0; i < listItems.length; i++){
        listItems[i].innerHTML += (`<div class="idt-list__item__icon"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M17.71 11.29a1 1 0 0 0-1.42 0L13 14.59V7a1 1 0 0 0-2 0v7.59l-3.29-3.3a1 1 0 0 0-1.42 1.42l5 5a1 1 0 0 0 .33.21a.94.94 0 0 0 .76 0a1 1 0 0 0 .33-.21l5-5a1 1 0 0 0 0-1.42" /></svg></div>`)
        listItems[i].addEventListener("click", ()=>{
            listItems[i].classList.toggle("active");
        })
    }
    
    for (let i = 0; i < listItemsContent.length; i++){
        listItemsContent[i].addEventListener("click", (e)=>{
            e.stopPropagation();
        })
    }
}