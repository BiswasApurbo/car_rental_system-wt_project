<?php
session_start();
require_once "../model/LoyaltyModel.php"; 

if (!isset($_SESSION['user_id'])) $_SESSION['user_id'] = 1;
$user_id = $_SESSION['user_id'];

$currentData = getLoyaltyPoints($user_id);
$currentPoints = $currentData['points'];
$tier = $currentData['tier'];
$maxPoints = 5000;

function calcProgress($tier, $points, $maxPoints) {
    if ($tier === "Platinum") return ["nextTierPoints"=>"Max tier reached 🎉","progress"=>($points/$maxPoints)*100];
    elseif ($tier === "Gold") return ["nextTierPoints"=>4000-$points,"progress"=>($points/$maxPoints)*100];
    else return ["nextTierPoints"=>2000-$points,"progress"=>($points/$maxPoints)*100];
}
$progressData = calcProgress($tier,$currentPoints,$maxPoints);
$nextTierPoints = $progressData['nextTierPoints'];
$progress = $progressData['progress'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Loyalty Program</title>
<link rel="stylesheet" href="../asset/LoyaltyProgram.css">
<style>
.points-bar { width:100%; background:#eee; border-radius:8px; height:20px; margin-bottom:10px; }
.points-progress { height:100%; background:#1f6feb; border-radius:8px; }
.message { margin:10px 0; font-size:14px; }
.green { color:green; }
.red { color:red; }
</style>
</head>
<body>
<div class="form-wrapper">
<fieldset>
<h1>Loyalty Program Dashboard</h1>
<div id="messageArea"></div>

<h2>💎 Points Tracker</h2>
<p>Current Points: <strong id="currentPoints"><?= $currentPoints ?></strong> / <?= $maxPoints ?></p>
<p>Current Tier: <strong id="currentTier"><?= $tier ?></strong></p>
<p>Points to Next Tier: <strong id="pointsNextTier"><?= $nextTierPoints ?></strong></p>
<div class="points-bar">
    <div class="points-progress" id="pointsProgress" style="width: <?= $progress ?>%;"></div>
</div>

<h2>🎁 Reward Catalog</h2>
<table>
<tr><th>Reward</th><th>Points Required</th><th>Action</th></tr>
<?php
$rewards = [
    ["name"=>"Free Coffee","points"=>100],
    ["name"=>"Discount Voucher tk.10000","points"=>500],
    ["name"=>"Free Upgrade","points"=>1000]
];
foreach($rewards as $r): ?>
<tr>
<td><?= $r['name'] ?></td>
<td><?= $r['points'] ?></td>
<td><button onclick="redeemPoints(<?= $r['points'] ?>)">Redeem</button></td>
</tr>
<?php endforeach; ?>
</table>

<h2>🏆 Tier Benefits</h2>
<ul>
<li>Silver: 0-1999 points – Basic benefits</li>
<li>Gold: 2000-3999 points – Free upgrades, priority support</li>
<li>Platinum: 4000-5000 points – Exclusive rewards, VIP support</li>
</ul>

<br><br>
<input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
<input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
</fieldset>
</div>

<script>
function redeemPoints(points) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST","../controller/Loyalty_handler.php",true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.onload = function() {
        if(xhr.status === 200){
            try{
                const res = JSON.parse(xhr.responseText);
                const msgArea = document.getElementById("messageArea");
                if(res.success){
                    msgArea.innerHTML = "<p class='message green'>✅ "+res.message+"</p>";
                    document.getElementById("currentPoints").textContent = res.points;
                    document.getElementById("currentTier").textContent = res.tier;
                    document.getElementById("pointsNextTier").textContent = res.nextTierPoints;
                    document.getElementById("pointsProgress").style.width = res.progress+"%";
                }else{
                    msgArea.innerHTML = "<p class='message red'>❌ "+res.message+"</p>";
                }
            }catch(e){
                alert("Error parsing server response");
            }
        }else{
            alert("Server error");
        }
    };
    xhr.send("redeemPoints="+points);
}
</script>
</body>
</html>
