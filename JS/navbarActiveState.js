let nav = document.getElementById("navbarNavAltMarkup");
let links = nav.getElementsByClassName("nav-link");
console.log(links);
let url = window.location.href;
console.log(url)
console.log(links[0].href)
for (let i = 0; i < links.length; i++) {
    if(links[i].href === url){
        links[i].classList.add("active");
      }
}

