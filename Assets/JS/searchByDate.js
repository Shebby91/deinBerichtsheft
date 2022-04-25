document.getElementById("date").addEventListener("change", function(event){

  var input, filter, tr, oldDate, i, newDate;
  input = document.getElementById("date");
  filter = input.value;
  if(filter.length == 0) {
    newDate = "tt.mm.jj";
  } else {
    oldDate = filter.split("-")
    newDate = oldDate[2] +"."+ oldDate[1] +"." + oldDate[0];
  }

  let col = document.getElementsByClassName("col");

  tr = document.getElementsByClassName("card-title");

  for (i = 0; i < tr.length; i++) {
      if (tr[i].innerHTML.slice(13,30).indexOf(newDate) > -1) {
        col[i].style.display = "";
      } else {
        if(col[i].classList.contains("action")){
            col[i].style.display = "";
        } else {
            col[i].style.display = "none";
        }
      }
      if(newDate == "tt.mm.jj") {
        col[i].style.display = "block";
      }
    }
});
