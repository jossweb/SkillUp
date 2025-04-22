var mdEditor = document.getElementById('md-editor');
var preview = document.getElementById('preview');
var edit = document.getElementById('edit');
var toEdit = document.getElementById('to-edit');
var toPrev = document.getElementById('to-preview');

const urlParams = new URLSearchParams(window.location.search);
const cours = urlParams.get('cours');

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

mdEditor.value = GetMd();

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

//drag and drop system 
mdEditor.addEventListener('dragover', (e) => {
  e.preventDefault();
  mdEditor.style.border = "2px dashed #aaa";
});

mdEditor.addEventListener('dragleave', () => {
  mdEditor.style.border = "";
});

mdEditor.addEventListener('drop', async (e) => {
  e.preventDefault();
  mdEditor.style.border = "3px";
  const files = e.dataTransfer.files;
  if (!files.length) return;
  const file = files[0];

  if (!file.type.startsWith('image/')) {
    alert("Le fichier n'est pas une image. \n Veuillez entrer une image valide");
    return;
  }

  const reader = new FileReader();
  reader.onload = async function () {
    const base64 = reader.result; 
    const markdownImage = `![image](${base64})`;

    const savedPath = await SaveImg(markdownImage);

    if (savedPath) {
      const cursorPos = mdEditor.selectionStart;
      const before = mdEditor.value.substring(0, cursorPos);
      const after = mdEditor.value.substring(cursorPos);
      const markdownFinal = `[image](${savedPath})`; // tu peux aussi personnaliser ici
      mdEditor.value = before + markdownFinal + after;
    } else {
      alert("Erreur lors de la sauvegarde de l'image.");
    }
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