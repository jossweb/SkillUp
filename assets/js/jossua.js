function toggleForm(toregister){
    var loginForm = document.getElementById('login-form');
    var registerForm = document.getElementById('register-form');
    if(toregister){
        toggleIsSelected(true, loginForm, registerForm);
        registerForm.style.display = 'flex';
        loginForm.style.display = 'none';
    }else{
        toggleIsSelected(false, loginForm, registerForm);

        registerForm.style.display = 'none';
        loginForm.style.display = 'flex';
    }
}
function toggleStats(tocours){
    var stats = document.getElementById('stats');
    var cours = document.getElementById('cours');
    if(tocours){
        toggleIsSelected(true, stats, cours);
        cours.style.display = 'flex';
        stats.style.display = 'none';
    }else{
        toggleIsSelected(false, stats, cours);

        cours.style.display = 'none';
        stats.style.display = 'flex';
    }
}
function toggleIsSelected(registerIsClicked){
    var loginBtn = document.getElementById('login-btn');
    var registerBtn = document.getElementById('register-btn');
    if(registerIsClicked){
        loginBtn.classList.remove('isSelected');
        registerBtn.classList.add('isSelected');
    }else{
        loginBtn.classList.add('isSelected');
        registerBtn.classList.remove('isSelected');
    }
}
//dash
function OpenPopup(id){
    blurredBg.style.display = "flex";
    blurredBg.style.opacity = 1;
    popup.style.display = "flex";
    popup.style.opacity = 1;
    hiddenInput.value = id;
    
  }
  function CloseDeleteCheckDash(){
    blurredBg.style.display = "none";
    blurredBg.style.opacity = 0;
    popup.style.display = "none";
    popup.style.opacity = 0;
  }
//profile
function ShowLoading(){
    loading.style.display = "block";
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
function OpenAvatarPopup() {
    blurredBg.style.opacity = "90%";
    newPP.style.opacity = "1";
    newPP.style.display = "block";
    localStorage.setItem('avatarPopupOpen', 'true'); 
  }
function SaveImg(img){
    return fetch('../api/UploadImage.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
          token: getCookie('token_api'),
          image: img,
          chapter: getCookie('activeChapId'),
          cours : cours
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
          return data.file;
      } else {
          return false;
      }
    })
    .catch(() => false);
}
function save(){
    fetch('../api/SaveMd.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
          token: getCookie('token_api'),
          markdown: mdEditor.value,
          chapter: getCookie('activeChapId'),
          cours : cours
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
          console.log("Fichier enregistré avec succès !");
      } else {
          console.error("Erreur :", data.error);
      }
    })
  
    fetch('../api/CleanImg.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
          token: getCookie('token_api'),
          markdown: mdEditor.value,
          chapter: getCookie('activeChapId'),
          cours : cours
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
          console.log("Fichier enregistré avec succès !");
      } else {
          console.error("Erreur :", data.error);
      }
    })
  }
function ChangeActiveChapter(id) {
    document.cookie = `activeChapId=${id}; path=./; max-age=3600`;
    window.location.href = window.location.href;
}
function AddToMd(addString){
    if (addString.includes("#")) {
        mdEditor.value += "\n" + addString;
    }else{
        mdEditor.value += " " + addString;
    }
}
function ShowPreview(){
    toEdit.style.backgroundColor = "transparent";
    toPrev.style.backgroundColor = "var(--skillup-pink)";
    edit.style.display = "none";
    edit.style.opacity = "0";
    preview.style.display = "block";
    preview.style.opacity = "1";

    var html = DOMPurify.sanitize(marked.parse(mdEditor.value));
    document.getElementById('md-output').innerHTML = html;

}
function ShowEdit(){
    toEdit.style.backgroundColor = "var(--skillup-pink)";
    toPrev.style.backgroundColor = "transparent";
    edit.style.display = "block";
    edit.style.opacity = "1";
    preview.style.display = "none";
    preview.style.opacity = "0";
}
function ResponsiveSys(way){
    if(way){
      leftBar.style.display = "block";
      mainPart.style.display = "none";
    }else{
      leftBar.style.display = "none";
      mainPart.style.display = "block";
    }
  }

function getCookie(name) {
    const cookies = document.cookie.split("; ");
    for (const cookie of cookies) {
        const [key, value] = cookie.split("=");
        if (key === name) return decodeURIComponent(value);
    }
    return null;
}
function GetMd(){
  fetch('../api/GetMd.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({
      token: getCookie('token_api'),
      chapter: getCookie('activeChapId'),
      cours: cours
    })
  })
  .then(response => response.text())
.then(text => {
  try {
    const data = JSON.parse(text);
    mdEditor.value = data.content;
  } catch (e) {
    console.error("Erreur JSON :", e);
  }
});
}
//api
function copy(element){
    navigator.clipboard.writeText(element);
    alert("copié dans le presse-papier !");
  }