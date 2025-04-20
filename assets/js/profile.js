    //get elements
    var blurredBg = document.getElementById('blurred-bg');
    var deleteMyAccount = document.getElementById('delete');
    var deletePopup = document.getElementById('delete-check');
    var okClosePopup = document.getElementById('Okk');
    var newPP = document.getElementById('new-pp');
    var loading = document.getElementById('loading-emo');
    var message = document.getElementById('message-pop')

    function ShowLoading(){
        loading.style.display = "block";
    }
    function OpenAvatarPopup() {
        blurredBg.style.opacity = "90%";
        newPP.style.opacity = "1";
        newPP.style.display = "block";
        localStorage.setItem('avatarPopupOpen', 'true'); 
    }
    function CloseAvatarPopup() {
        blurredBg.style.opacity = "0";
        newPP.style.opacity = "0";
        newPP.style.display = "none";
        localStorage.removeItem('avatarPopupOpen'); 
    }
    function CheckOpenCondition(){
        var nameValue = document.getElementById("new-name").value;
        var firstnameValue = document.getElementById("new-firstname").value;
        if(nameValue && firstnameValue){
            OpenMessagePopup();
        }
    }
    function OpenMessagePopup(){
        blurredBg.style.opacity = "90%";
        message.style.opacity = "1";
        message.style.display = "block";
        localStorage.setItem('messagePopOpen', 'true');

    }
    function CloseMessagePopup(){
        blurredBg.style.opacity = "0";
        message.style.opacity = "0";
        message.style.display = "none";
        localStorage.removeItem('messagePopOpen');
    }
    function OpenDeleteCheck(){
        blurredBg.style.opacity = "90%";
        deletePopup.style.opacity = "1";
        deletePopup.style.display = "block";
        localStorage.setItem('deletePopup', 'true');
    }
    function CloseDeleteCheck(){
        blurredBg.style.opacity = "0";
        deletePopup.style.opacity = "0";
        deletePopup.style.display = "none";
        localStorage.removeItem('deletePopup');
        }
    document.addEventListener("DOMContentLoaded", function () {
        if (localStorage.getItem('avatarPopupOpen') === 'true') {
            OpenAvatarPopup(); 
        }
        if(localStorage.getItem('messagePopOpen') === 'true'){
            OpenMessagePopup();
        }
        if(localStorage.getItem('deletePopup') === 'true'){
            OpenDeleteCheck();
        }
    });