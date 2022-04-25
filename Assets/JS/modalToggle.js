
var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'), {})
var wrapper = document.getElementById("wrapper");

myModal.show();
setTimeout(function(){ myModal.hide(); }, 1250);
setTimeout(function(){ wrapper.style.display = "block"; }, 1250);



