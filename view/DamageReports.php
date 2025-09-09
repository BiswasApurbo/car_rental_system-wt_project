<?php
session_start();
require_once "../model/DamageModel.php"; 
if (!isset($_SESSION['user_id'])) $_SESSION['user_id'] = 1;
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vehicle Damage Report</title>
<link rel="stylesheet" href="../asset/DamageReportsdesign.css">
</head>
<body>
<div class="form-wrapper">
<form id="damageForm" enctype="multipart/form-data">
<fieldset>
<h1>Vehicle Damage Report</h1>

<div id="messageArea"></div>

<label>Upload Vehicle Photo</label>
<input type="file" id="photoUpload" accept="image/*" onchange="loadImage(event)">
<p id="instruction"></p>

<div id="canvasContainer">
    <canvas id="canvas"></canvas>
</div>
<button type="button" id="undoBtn" onclick="undoLastCircle()">Undo Last Mark</button>
<input type="hidden" id="damageMarks">
<input type="hidden" id="markedPhoto">
<p id="canvasError"></p>

<label>Customer Signature</label>
<canvas id="signatureCanvas"></canvas>
<button type="button" id="clearSigBtn" onclick="clearSignature()">Clear Signature</button>
<input type="hidden" id="signature">
<p id="sigError"></p>

<button type="button" onclick="submitReport()">Submit Report</button>
<br><br>
<input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
<input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
</fieldset>
</form>
</div>

<script>
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');
const image = new Image();
let circles = [];
let isDrawing = false;
let startX, startY;
let isImageLoaded = false;

const sigCanvas = document.getElementById('signatureCanvas');
const sigCtx = sigCanvas.getContext('2d');
let drawing = false;

function loadImage(event){
    const file = event.target.files[0];
    if(!file) return;
    const reader = new FileReader();
    reader.onload = function(e){
        image.onload = function(){
            const scale = image.width > 800 ? 800/image.width : 1;
            canvas.width = image.width*scale;
            canvas.height = image.height*scale;
            canvas.style.display = "block";
            isImageLoaded = true;
            drawCanvas();
            document.getElementById('instruction').innerText = "Click and drag on image to circle the damaged area.";
        };
        image.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

canvas.addEventListener("mousedown",(e)=>{
    if(!isImageLoaded) return;
    const rect = canvas.getBoundingClientRect();
    startX = e.clientX-rect.left;
    startY = e.clientY-rect.top;
    isDrawing=true;
});
canvas.addEventListener("mouseup",(e)=>{
    if(!isDrawing) return;
    const rect = canvas.getBoundingClientRect();
    const endX = e.clientX-rect.left;
    const endY = e.clientY-rect.top;
    const radius = Math.sqrt(Math.pow(endX-startX,2)+Math.pow(endY-startY,2))/2;
    const centerX=(startX+endX)/2;
    const centerY=(startY+endY)/2;
    circles.push({x:centerX,y:centerY,r:radius,time:new Date().toISOString()});
    isDrawing=false;
    drawCanvas();
    document.getElementById('canvasError').innerText="Damage area marked."; 
    document.getElementById('canvasError').style.color="green";
});

function drawCanvas(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.drawImage(image,0,0,canvas.width,canvas.height);
    for(const c of circles){
        ctx.beginPath();
        ctx.arc(c.x,c.y,c.r,0,Math.PI*2);
        ctx.strokeStyle="red";
        ctx.lineWidth=2;
        ctx.stroke();
    }
}

function undoLastCircle(){
    if(circles.length>0){ circles.pop(); drawCanvas(); document.getElementById('canvasError').innerText="Last mark removed."; document.getElementById('canvasError').style.color="orange";}
}

sigCanvas.width=sigCanvas.offsetWidth;
sigCanvas.height=100;

sigCanvas.addEventListener('mousedown',()=>drawing=true);
sigCanvas.addEventListener('mouseup',()=>{drawing=false; sigCtx.beginPath();});
sigCanvas.addEventListener('mousemove',drawSignature);

function drawSignature(e){
    if(!drawing) return;
    const rect = sigCanvas.getBoundingClientRect();
    const x = e.clientX-rect.left;
    const y = e.clientY-rect.top;
    sigCtx.lineWidth=2;
    sigCtx.lineCap='round';
    sigCtx.strokeStyle='black';
    sigCtx.lineTo(x,y);
    sigCtx.stroke();
    sigCtx.beginPath();
    sigCtx.moveTo(x,y);
}

function clearSignature(){ sigCtx.clearRect(0,0,sigCanvas.width,sigCanvas.height); document.getElementById('sigError').innerText=""; }

function submitReport(){
    let valid=true;
    if(!isImageLoaded){document.getElementById('canvasError').innerText="Please upload a vehicle image first."; document.getElementById('canvasError').style.color="red"; valid=false;}
    else if(circles.length===0){document.getElementById('canvasError').innerText="Please mark at least one damage area."; document.getElementById('canvasError').style.color="red"; valid=false;}

    const blank=document.createElement('canvas'); blank.width=sigCanvas.width; blank.height=sigCanvas.height;
    const sigData=sigCanvas.toDataURL();
    const blankData=blank.toDataURL();
    if(sigData===blankData){document.getElementById('sigError').innerText="Signature is required."; document.getElementById('sigError').style.color="red"; valid=false;}
    else{document.getElementById('signature').value=sigData; document.getElementById('sigError').innerText="";}

    document.getElementById('damageMarks').value=JSON.stringify(circles);
    document.getElementById('markedPhoto').value=canvas.toDataURL('image/png');

    if(!valid) return;

    const formData=new FormData();
    formData.append('vehiclePhoto',document.getElementById('photoUpload').files[0]);
    formData.append('damageMarks',document.getElementById('damageMarks').value);
    formData.append('markedPhoto',document.getElementById('markedPhoto').value);
    formData.append('signature',document.getElementById('signature').value);

    const xhr=new XMLHttpRequest();
    xhr.open('POST','../controller/Damage_handler.php',true);
    xhr.onload=function(){
        if(xhr.status===200){
            document.getElementById('messageArea').innerHTML=xhr.responseText;
            window.scrollTo(0,0);
        }else{ alert("Server error"); }
    };
    xhr.send(formData);
}
</script>
</body>
</html>
