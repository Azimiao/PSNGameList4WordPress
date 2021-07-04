let PsnGameListGrid = null;

let PsnGameListConfigCache = {
    apibase: "",
    nextOffset: 0,
    theCount: 0,
    limit: 3,
    padidng:10,
    loadingImg:""
}

function PsnGameListConfigInit(configUrl){

    PsnGameListConfigCache.nextOffset = 0;
    PsnGameListConfigCache.theCount = 0;
    PsnGameListConfigCache.emptyElements = new Array();

    fetch(`${configUrl}?action=GetGameListShowConfig`).then(configRes=>{
        configRes.json().then(configJson=>{
            PsnGameListConfigCache.apibase = configJson.apibase;
            PsnGameListConfigCache.limit = configJson.limit;
            PsnGameListConfigCache.padidng = configJson.padding;
            PsnGameListConfigCache.loadingImg = configJson.loadingImg;
            GetTrophyTitles();
        })
    }).catch(e=>{
        console.error(e);
        alert(e);
    });
}

function GetTrophyTitles() {
    let btnT = document.getElementById("btnMore");
    btnT.setAttribute("disabled", "disabled");
    btnT.innerText = "正在获取";

    PsnGameListConfigCache.emptyElements = new Array();

    for (let i = 0; i < PsnGameListConfigCache.limit; i++) {
        let item = new DOMParser().parseFromString(`
<div class="PsnItem">
    <img  class="PsnIcon" src="${PsnGameListConfigCache.loadingImg}" style="height:180px" alt="">
    <div class="PsnTextInfo">
        <p class = "PsnTitle textLoading"></p>
        <span class = "PsnProgressBG">
            <span class = "PsnProgress textLoading">🏆</span>
        </span>
        <span class = "PsnLastPlayTime textLoading">⏰</span>
        <span class = "PsnPlatform textLoading" >🎮</span>
    </div>
</div>`, "text/html").querySelector(".PsnItem");
        document.getElementById("PsnItemContainer").appendChild(item);
        PsnGameListConfigCache.emptyElements.push(item);
    }

    ReflushGrid();
    console.log(`${PsnGameListConfigCache.apibase}?action=GetTrophyList&offset=${PsnGameListConfigCache.nextOffset}&limit=${PsnGameListConfigCache.limit}`);
    fetch(`${PsnGameListConfigCache.apibase}?action=GetTrophyList&offset=${PsnGameListConfigCache.nextOffset}&limit=${PsnGameListConfigCache.limit}`)
        .then(res => {
            res.json().then(json => {
                if (json.hasOwnProperty("code")) {
                    if (json.code == 200) {
                        OnTrophyResult(json.data);
                    }
                }
            }).catch(e => {
                console.error(e);
                console.error(res);
                throw e;
            });
        }).catch(e => {
            console.error(e);
            alert(e);
        })
}

function formatDate(dateStr) {
    let date = new Date(dateStr)
    let year = date.getFullYear();
    let month = date.getMonth() + 1;
    let day = date.getDate();
    let hour = date.getHours();
    let minute = date.getMinutes();
    let second = date.getSeconds();
    return year + "-" + formatTen(month) + "-" + formatTen(day) + " " + formatTen(hour) + ":" + formatTen(minute) + ":" + formatTen(second);
}
function formatTen(num) {
    return num > 9 ? (num + "") : ("0" + num);
}
function OnTrophyResult(jsonObj) {
    PsnGameListConfigCache.nextOffset = jsonObj.nextOffset;
    if (jsonObj.hasOwnProperty("trophyTitles") && Array.isArray(jsonObj.trophyTitles)) {
        PsnGameListConfigCache.theCount = jsonObj.trophyTitles.length;
        for (let i = 0; i < jsonObj.trophyTitles.length; i++) {
            const element = jsonObj.trophyTitles[i];
            let emptyEle = PsnGameListConfigCache.emptyElements.shift();

            let imgE = emptyEle.querySelector(".PsnIcon");
            imgE.removeAttribute("style");
            imgE.addEventListener("load", function () {
                loadImgFinish(this);
            }.bind(imgE));
            // imgE.onload = "loadImgFinish(this)";
            imgE.src = element.trophyTitleIconUrl;

            let titleE = emptyEle.querySelector(".PsnTitle");
            titleE.innerText = element.trophyTitleName;

            let progressE = emptyEle.querySelector(".PsnProgress");
            progressE.style.width = element.progress + "%";
            progressE.innerText = `🏆 ${element.progress}%`;


            let lastPlayE = emptyEle.querySelector(".PsnLastPlayTime");
            let lastDate = new Date(element.lastUpdatedDateTime);

            lastPlayE.innerText = `⏰ ${formatDate(lastDate)}`;

            let PsnPlatform = emptyEle.querySelector(".PsnPlatform");
            PsnPlatform.innerText = `🎮 ${element.trophyTitlePlatform}`;

            titleE.classList.remove("textLoading");
            progressE.classList.remove("textLoading");
            lastPlayE.classList.remove("textLoading");
            PsnPlatform.classList.remove("textLoading");

        }
        if (PsnGameListConfigCache.emptyElements.length > 0) {
            let z = document.getElementById("PsnItemContainer");
            while (PsnGameListConfigCache.emptyElements.length > 0) {
                let k = PsnGameListConfigCache.emptyElements.pop();
                z.removeChild(k);
            }
        }
    }
    ReflushGrid();

    this.ReflushNextBtn();


    document.getElementById("btnMore").scrollIntoView({
        behavior: "smooth",
        block: "nearest"
    });
}

function loadImgFinish(a) {
    a.style.backgroundColor = "#333645";
    PsnGameListConfigCache.theCount--;
    if (PsnGameListConfigCache.theCount >= 0) {
        ReflushGrid();
    }
    if (PsnGameListConfigCache.theCount <= 0) {
        document.getElementById("btnMore").scrollIntoView({
            behavior: "smooth",
            block: "nearest"
        });
    }
}

function ReflushGrid() {
    PsnGameListGrid = new Minigrid({
        container: '.PsnItemContainer',
        item: '.PsnItem',
        gutter: PsnGameListConfigCache.padidng
    });
    PsnGameListGrid.mount();
}
function ReflushNextBtn() {
    let btnT = document.getElementById("btnMore");
    if (PsnGameListConfigCache.nextOffset != null && PsnGameListConfigCache.nextOffset > 0) {
        btnT.removeAttribute("disabled");
        btnT.innerText = "加载更多";
    } else {
        btnT.setAttribute("disabled", "disabled");
        btnT.innerText = "没有了哦";
    }
}

window.addEventListener("resize", (a) => {
    if(PsnGameListGrid != null)
    PsnGameListGrid.mount();
})
//   grid.mount();