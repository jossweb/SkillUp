var mdEditor = document.getElementById('md-editor');
var preview = document.getElementById('preview');
var edit = document.getElementById('edit');
var toEdit = document.getElementById('to-edit');
var toPrev = document.getElementById('to-preview');

function getCookie(name) {
    const cookies = document.cookie.split("; ");
    for (const cookie of cookies) {
        const [key, value] = cookie.split("=");
        if (key === name) return decodeURIComponent(value);
    }
    return null;
}

mdEditor.value = getCookie("activeChapMd");

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

    var html = marked.parse(mdEditor.value);
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

//drag and drop system 
mdEditor.addEventListener('dragover', (e) => {
  e.preventDefault();
  mdEditor.style.border = "2px dashed #aaa";
});

mdEditor.addEventListener('dragleave', () => {
  mdEditor.style.border = "";
});

mdEditor.addEventListener('drop', (e) => {
  e.preventDefault();
  mdEditor.style.border = "";

  const files = e.dataTransfer.files;
  if (!files.length) return;

  const file = files[0];
  if (!file.type.startsWith('image/')) {
    alert("Le fichier n'est pas une image.");
    return;
  }

  const reader = new FileReader();
  reader.onload = function () {
    const base64 = reader.result; 
    const cursorPos = mdEditor.selectionStart;
    const before = mdEditor.value.substring(0, cursorPos);
    const after = mdEditor.value.substring(cursorPos);
    const markdownImage = `![image](${base64})`;

    mdEditor.value = before + markdownImage + after;
  };
  reader.readAsDataURL(file);
});

function save(){
  fetch('../api/SaveMd.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({
        token: token_api,
        markdown: mdEditor.value,
        chapter: activeChapId
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