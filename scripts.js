//売約ボタンを押した時の処理
function abtpPromptForName(postID, postTitle) {
  var name = prompt("苗字を入れてください。" + postTitle + "を売約します。");
  if (name === "") {
    abtpPromptForName(postID, postTitle);
  } else if (name !== null) {
    document.getElementById("abtp_name_" + postID).value = name;
    console.log(name);
    document.getElementById("submitButton_" + postID).click();
  }
}

//売約処理完了後の処理
function abtpShowToast() {
  let name = sessionStorage.getItem("abtp_name");
  let postTitle = sessionStorage.getItem("abtp_post_title");

  if (name && postTitle) {
    abtpCreateToast(postTitle);
    //データを削除
    sessionStorage.removeItem("abtp_name");
    sessionStorage.removeItem("abtp_post_title");
  }
}

window.addEventListener("load", () => {
  abtpShowToast();
});

//Toast表示（要素）
function abtpCreateToast(postTitle) {
  let divEl = document.createElement("div");
  let text = postTitle + "が売約されました";

  let innerHTML =
    `
    <div style="position:fixed;top:16px;left:16px;right:16px; color:black; background:#dbffdb; padding:1em; text-align:center">
    ` +
    text +
    `
    <button id="abtpCreateToastClose" onclick="abtpRemoveToast()" style="background:transparent;border:none; width:44px;height:44px;cursor:pointer">✖</button>
    </div>
    `;
  divEl.id = "abtpCreateToast";
  divEl.innerHTML = innerHTML;
  document.body.appendChild(divEl);
}

//Toast削除
function abtpRemoveToast() {
  let toast = document.getElementById("abtpCreateToast");
  toast.remove();
}
