let daysForSearch = []
window.onload = function(){
  window.addEventListener('resize', updateOrientation);
  document.getElementById("date").addEventListener("change", searchByDate);
  document.getElementById("date2").addEventListener("change", searchByDate);
  document.getElementById("switchCardTable").addEventListener("click", switchIcon);
  updateOrientation();
  getView();
};

function switchIcon() {
  let switchChildren = document.getElementById("switchCardTable").children; 
  let table = document.getElementById("table-container");
  let cards = document.getElementsByClassName("content-card");

  for (let index = 0; index < switchChildren.length; index++) {
      if(switchChildren[index].classList.contains("d-none")) {
          switchChildren[index].classList.remove("d-none");
      } else {
          switchChildren[index].classList.add("d-none");
      }
  }

  if(switchChildren[0].classList.contains("d-none")) {
      table.classList.remove("d-none");
      for (let index = 0; index < cards.length; index++) {
          cards[index].classList.add("d-none"); 
      }
  } else {
      table.classList.add("d-none");
      for (let index = 0; index < cards.length; index++) {
          cards[index].classList.remove("d-none");   
      }
  }
  
  let lastState = "table";
  if(table.classList.contains("d-none")){
    lastState = "card";
  }

  setView(lastState); 
  searchByDate(daysForSearch);
}

function searchByDate() {
  var inputStart, filterStart, tr, oldDate, i, newDate, col;
  let switchChildren = document.getElementById("switchCardTable").children;

  for (let index = 0; index < switchChildren.length; index++) {
    if(switchChildren[index].classList.contains("d-none")) {
      col = document.getElementsByClassName("content-card");
      tr = document.getElementsByClassName("card-title-date");
    } else {
      col = document.getElementsByClassName("date-check");
      tr = document.getElementsByClassName("table-date");
    }
  }

  inputStart = document.getElementById("date");
  filterStart = inputStart.value;


 
  if(filterStart.length == 0) {
    newDate = "tt.mm.jj";
    document.getElementById("date2").value = "";
    document.getElementById("date2").disabled = "true";
  } else {
    document.getElementById("date2").removeAttribute("disabled");
    oldDate = filterStart.split("-")
    newDate = oldDate[2] +"."+ oldDate[1] +"." + oldDate[0];
  }
  

  inputEnd = document.getElementById("date2");
  filterEnd = inputEnd.value;

  if(searchBetweenDates().length > 0){
    for (i = 0; i < tr.length; i++) {
      col[i].style.display = "none";
    }
    for (let j = 0; j < searchBetweenDates().length; j++) {
      for (i = 0; i < tr.length; i++) {
        if(tr[i].tagName == "A") {
          if(tr[i].innerHTML == searchBetweenDates()[j]){
            col[i].style.display = "table-row";
          }
        } else if (tr[i].tagName == "H5"){
          if(tr[i].innerHTML.slice(13,30) == searchBetweenDates()[j]){
            col[i].style.display = "block";
          }
        }
      }
    }
  }
  for (i = 0; i < tr.length; i++) {
    if(newDate == "tt.mm.jj") {            
      if(col[i].tagName == "TR"){
          col[i].style.display = "table-row";
      } else{
          col[i].style.display = "block";
      }
    }
  }
}

function setView(lastState) {
  const xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET", "index.php?controller=Report&do=setView&q=" + lastState);
  xmlhttp.send();
}

function getView() {
  const xmlhttp = new XMLHttpRequest();
  xmlhttp.onload = function() {
    if(this.responseText == "true") {
      let cards = document.getElementsByClassName("content-card");
      let switchChildren = document.getElementById("switchCardTable").children;
      let table = document.getElementById("table-container");
      switchChildren[0].classList.add("d-none");
      switchChildren[1].classList.remove("d-none");
      for (let index = 0; index < cards.length; index++) {
        cards[index].classList.add("d-none"); 
      }
      table.classList.remove("d-none");

    } else if(this.responseText == "false"){
      let cards = document.getElementsByClassName("content-card");
      let switchChildren = document.getElementById("switchCardTable").children;
      let table = document.getElementById("table-container");
      switchChildren[0].classList.remove("d-none");
      switchChildren[1].classList.add("d-none");
      for (let index = 0; index < cards.length; index++) {
        cards[index].classList.remove("d-none"); 
      }
      table.classList.add("d-none");
    }
  }
  xmlhttp.open("GET", "index.php?controller=Report&do=getView");
  xmlhttp.send();
}

function updateOrientation() {
  let tableContainer = document.getElementById("table-container");
  var mql = window.matchMedia("(orientation: portrait)");

  if(mql.matches) {  
    if (window.innerWidth >= 576) { 
      tableContainer.style.height = window.innerHeight - (window.innerHeight*1.9) + "px";
      tableContainer.style.height = 280+ "px";
    }
    if (window.innerWidth >= 768) { 
      tableContainer.style.height = window.innerHeight -475 + "px";
    }
  } else {  
    if (window.innerHeight >= 684) { 
      tableContainer.style.height = window.innerHeight - (window.innerHeight*1.9) + "px";
    } 
    tableContainer.style.height = window.innerHeight -475 + "px";
  }
}

function searchBetweenDates() {
  
    inputStartDate = document.getElementById("date").value;
    inputEndDate = document.getElementById("date2").value;

    let newStartDate;

    if(inputStartDate.length == 0) {
      newStartDate = "tt.mm.jj";
    } else {
      oldDate = inputStartDate.split("-")
      newStartDate = oldDate[2] +"."+ oldDate[1] +"." + oldDate[0];
    }

    var startDate = new Date(inputStartDate); 
    var endDate = new Date(inputEndDate); 

    let oneDay = 86400000;

    var difference = startDate.getTime() - endDate.getTime();
    var differenceDays = Math. ceil(difference / (1000 * 3600 * 24)) * -1; 
    
    let datesBetween = [startDate];
    let datesBetweenFormatted = [newStartDate]

    for (let i = 0; i < differenceDays; i++) {
          let tmp = datesBetween[i].getTime() + oneDay
          let date = new Date(tmp)
    
          let newDate = date.toLocaleDateString("de-DE", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
          })
          datesBetween.push(date)
          datesBetweenFormatted.push(newDate)
    }

    daysForSearch = datesBetweenFormatted
    return daysForSearch;
}