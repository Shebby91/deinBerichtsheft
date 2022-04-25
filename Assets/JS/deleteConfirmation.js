document.getElementById("delete").addEventListener("click", function(event){
  let text = "Bist du dir sicher das du das Berichtsheft löschen möchtest?";
  if (confirm(text) !== true) {
    event.preventDefault()
  } else {
    event.submit()
  }
});