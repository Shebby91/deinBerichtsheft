let nav = document.getElementById("navbarNavAltMarkup");
let links = nav.getElementsByClassName("nav-link");
let url = window.location.href;

for (let i = 0; i < links.length; i++) {
    if(links[i].href === url){
        links[i].classList.add("active");
      }
}

