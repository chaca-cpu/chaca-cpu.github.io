<?php
$main_url = "http://kusumagy.unaux.com";
$artworks_url = $main_url . "/data/artworks.json";
$config_url = $main_url . "/data/config.json";

function fetchData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$artworks = json_decode(fetchData($artworks_url), true) ?? [];
$config = json_decode(fetchData($config_url), true) ?? [];
$link_ke_dark = "http://" . ($config['sub1'] ?? '#');
$wa_number = $config['wa'] ?? '';

// --- LOGIKA FILTER ---
$sort = $_GET['sort'] ?? 'newest';
if ($sort == 'oldest') {
    usort($artworks, fn($a, $b) => ($a['id'] ?? 0) - ($b['id'] ?? 0));
} else {
    usort($artworks, fn($a, $b) => ($b['id'] ?? 0) - ($a['id'] ?? 0));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kusumagy - Light</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --purple: #BE8CFF; 
            --accent: #7C5BA7; 
            --bg: #ffffff; 
            --light-grey: #f0f0f0; 
            --black: #000000; 
        }
        
        body, html { margin: 0; padding: 0; font-family: 'Josefin Sans', sans-serif; background: var(--bg); color: #2d2d2d; scroll-behavior: smooth; }
        
        /* HEADER */
        header { position: fixed; top: 0; width: 100%; height: 70px; display: flex; justify-content: space-between; align-items: center; padding: 0 25px; box-sizing: border-box; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); z-index: 4000; border-bottom: 1px solid rgba(190, 140, 255, 0.2); }
        .logo-nav { width: 32px; height: 32px; }
        .menu-trigger { cursor: pointer; display: flex; flex-direction: column; gap: 5px; }
        .menu-trigger span { display: block; height: 2px; background: var(--black); width: 25px; }
        
        /* SIDEBAR */
        .sidebar { position: fixed; top: 15px; right: -100%; width: 240px; height: calc(100vh - 30px); background: #fff; z-index: 5000; transition: 0.5s; display: flex; flex-direction: column; padding: 60px 30px; box-sizing: border-box; border-radius: 30px 0 0 30px; border: 1px solid #f0f0f0; }
        .sidebar.active { right: 0; }
        .sidebar a { text-decoration: none; color: var(--black); font-size: 0.75rem; letter-spacing: 1px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; text-transform: uppercase; font-family: 'Inter', sans-serif; font-weight: 600; }
        
        /* MAIN CONTENT */
        main { padding: 100px 15px 50px; max-width: 1200px; margin: 0 auto; text-align: center; }
        .filter-nav { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; font-family: 'Inter', sans-serif; }
        .filter-nav a { text-decoration: none; color: #999; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; transition: 0.3s; padding: 5px 10px; border-bottom: 2px solid transparent; }
        .filter-nav a.active { color: var(--purple); border-bottom: 2px solid var(--purple); }

        /* GRID */
        .grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 50px; }
        .item { aspect-ratio: 1/1; border-radius: 12px; overflow: hidden; cursor: pointer; background: #f8f5ff; }
        .item img { width: 100%; height: 100%; display: block; object-fit: cover; transition: 0.4s; opacity: 0.9; }
        .item:hover img { transform: scale(1.05); opacity: 1; }

        @media (max-width: 600px) {
            .grid { grid-template-columns: repeat(3, 1fr); gap: 8px; }
        }
        
        /* SOSMED & MAIN BUTTON */
        .sosmed-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; }
        .sosmed-container a { width: 40px; height: 40px; background: var(--light-grey); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--black); text-decoration: none; font-size: 1.1rem; transition: 0.4s; border: 1px solid #e0e0e0; }

        .btn-contact-modern { 
            display: inline-block; 
            padding: 18px 60px; 
            background: var(--purple); 
            color: #ffffff; 
            text-decoration: none; 
            font-size: 0.7rem; 
            letter-spacing: 3px; 
            font-weight: 700; 
            border-radius: 50px; 
            transition: 0.4s; 
            text-transform: uppercase; 
            font-family: 'Inter', sans-serif; 
            border: none;
        }

        /* MODAL SYSTEM (REVISED FOR DESKTOP & MOBILE) */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: none; z-index: 6000; background: #fff; overflow-y: auto; padding: 0; }
        .modal-content { max-width: 1100px; margin: 0 auto; padding: 80px 20px 50px; box-sizing: border-box; }
        
        .modal-flex-container { display: flex; flex-direction: row; gap: 50px; align-items: flex-start; }
        .modal-info-side { flex: 1; position: sticky; top: 100px; display: flex; flex-direction: column; text-align: left; }
        .modal-media-side { flex: 1.5; display: flex; flex-direction: column; width: 100%; }

        .m-title-top { margin: 0 0 20px 0; font-size: 2.2rem; font-weight: 600; color: var(--purple); text-transform: uppercase; letter-spacing: 2px; }
        .m-media-stack { width: 100%; border-radius: 20px; object-fit: cover; margin-bottom: 20px; border: 1px solid var(--light-grey); }
        #m-yt-frame { width: 100%; aspect-ratio: 16/9; border-radius: 20px; border: 1px solid var(--light-grey); margin-bottom: 20px; display: none; }
        .m-desc-bottom { font-size: 1rem; line-height: 1.7; margin: 0 0 35px 0; color: #666; }
        
        .btn-order { 
            display: inline-block; 
            padding: 18px 45px; 
            background: var(--purple); 
            color: #fff !important; 
            text-decoration: none; 
            border-radius: 50px; 
            font-weight: 700; 
            font-family: 'Inter', sans-serif; 
            font-size: 0.7rem; 
            letter-spacing: 2px;
            text-transform: uppercase; 
            transition: 0.3s; 
            text-align: center; 
            border: none; 
            cursor: pointer; 
            width: fit-content;
        }

        .btn-close-modal { 
            cursor: pointer; 
            opacity: 0.5; 
            font-size: 0.75rem; 
            letter-spacing: 2px; 
            padding: 30px 0; 
            text-transform: uppercase; 
            font-weight: 400; 
            text-align: center; 
            color: var(--black); 
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* KHUSUS MOBILE VIEWPORT */
        @media (max-width: 900px) {
            .modal-content { padding-top: 60px; padding-bottom: 80px; }
            .modal-flex-container { flex-direction: column !important; align-items: center; gap: 0; }
            .modal-info-side { display: contents; } 

            .m-title-top { 
                order: 1; 
                text-align: center; 
                width: 100%; 
                font-size: 1.4rem; 
                margin-bottom: 30px; 
                letter-spacing: 1px;
            }
            .modal-media-side { order: 2; width: 100%; }
            .m-desc-bottom { 
                order: 3; 
                text-align: center; 
                width: 100%; 
                margin: 40px 0 30px 0; 
                font-size: 0.9rem;
                padding: 0 10px;
                box-sizing: border-box;
            }
            .btn-order { 
                order: 4; 
                width: auto; 
                min-width: 200px; 
                margin: 0 auto; 
                align-self: center;
            }
            .btn-close-modal { 
                order: 5; 
                width: 100%; 
                margin-top: 20px;
                opacity: 0.4;
            }
        }
    </style>
</head>
<body>

<header>
    <img src="<?= $main_url ?>/assets/logoweb/light.png" class="logo-nav">
    <div class="menu-trigger" onclick="toggleSidebar()">
        <span></span>
        <span style="width:15px; background:var(--black); height:2px; align-self:flex-end;"></span>
    </div>
</header>

<div class="sidebar" id="sidebar">
    <div onclick="toggleSidebar()" style="cursor:pointer; margin-bottom:40px; color:var(--black); font-weight:700;">CLOSE &times;</div>
    <a href="<?= $main_url ?>"><i class="fa-solid fa-house"></i> Home</a>
    <a href="<?= $link_ke_dark ?>"><i class="fa-solid fa-moon"></i> Dark Side</a>
    <a href="<?= $main_url ?>/aboutme.php"><i class="fa-solid fa-user"></i> About Me</a>
</div>

<main>
    <nav class="filter-nav">
        <a href="?sort=newest" class="<?= $sort == 'newest' ? 'active' : '' ?>">Terbaru</a>
        <a href="?sort=oldest" class="<?= $sort == 'oldest' ? 'active' : '' ?>">Terlama</a>
    </nav>

    <div class="grid">
        <?php foreach($artworks as $item): if(strtolower($item['side'] ?? '') == 'light'): ?>
            <div class="item" onclick='openModal(<?= json_encode($item) ?>)'>
                <img src="<?= $main_url ?>/assets/img/artworks/<?= $item['thumbnail'] ?>">
            </div>
        <?php endif; endforeach; ?>
    </div>

    <div class="sosmed-container">
        <a href="<?= $config['socials']['instagram'] ?? '#' ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a>
        <a href="<?= $config['socials']['tiktok'] ?? '#' ?>" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
        <a href="<?= $config['socials']['youtube'] ?? '#' ?>" target="_blank"><i class="fa-brands fa-youtube"></i></a>
        <a href="<?= $config['socials']['behance'] ?? '#' ?>" target="_blank"><i class="fa-brands fa-behance"></i></a>
        <a href="<?= $config['socials']['dribbble'] ?? '#' ?>" target="_blank"><i class="fa-brands fa-dribbble"></i></a>
    </div>

    <a href="https://wa.me/<?= str_replace('+', '', $wa_number) ?>" class="btn-contact-modern">Hit Me</a>
</main>

<div id="artModal" class="modal">
    <div class="modal-content">
        <!-- Flex Container untuk tata letak desktop & mobile -->
        <div class="modal-flex-container">
            
            <div class="modal-info-side">
                <h2 id="m-title" class="m-title-top"></h2>
                <p id="m-desc" class="m-desc-bottom"></p>
                <a id="m-link" href="#" target="_blank" class="btn-order">Request Artwork</a>
                <div onclick="closeModal()" class="btn-close-modal">
                    <i class="fa-solid fa-arrow-left" style="font-size:0.7rem;"></i> KEMBALI
                </div>
            </div>

            <div class="modal-media-side">
                <div id="media-stack-container"></div>
                <div id="v-cont" style="display:none;">
                    <video id="m-video" class="m-media-stack" muted loop playsinline autoplay>
                        <source src="" type="video/mp4">
                    </video>
                </div>
                <iframe id="m-yt-frame" src="" frameborder="0" allowfullscreen></iframe>
            </div>

        </div>
    </div>
</div>

<script>
    function toggleSidebar() { document.getElementById('sidebar').classList.toggle('active'); }
    
    function getYoutubeID(url) {
        if (!url) return null;
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match && match[2].length == 11) ? match[2] : null;
    }

    function openModal(data) {
        const root = "<?= $main_url ?>/assets/img/artworks/";
        document.getElementById('m-title').innerText = data.title;
        const mediaStack = document.getElementById('media-stack-container');
        mediaStack.innerHTML = "";

        if (data.images && typeof data.images === 'object') {
            Object.values(data.images).forEach(imgName => {
                if(imgName) {
                    let imgTag = document.createElement('img');
                    imgTag.src = root + imgName;
                    imgTag.className = 'm-media-stack';
                    mediaStack.appendChild(imgTag);
                }
            });
        } else {
            let imgTag = document.createElement('img');
            imgTag.src = root + (data.full_image || data.thumbnail);
            imgTag.className = 'm-media-stack';
            mediaStack.appendChild(imgTag);
        }
        
        const v = document.getElementById('m-video');
        if(data.video_media) { 
            document.getElementById('v-cont').style.display="block"; 
            v.src=root+data.video_media; 
            v.load(); 
        } else { 
            document.getElementById('v-cont').style.display="none"; 
        }

        const ytFrame = document.getElementById('m-yt-frame');
        const ytID = getYoutubeID(data.video_url);
        if (ytID) {
            ytFrame.src = "https://www.youtube.com/embed/" + ytID;
            ytFrame.style.display = "block";
        } else {
            ytFrame.src = "";
            ytFrame.style.display = "none";
        }

        document.getElementById('m-desc').innerText = data.description;
        document.getElementById('m-link').href = "https://wa.me/<?= str_replace('+', '', $wa_number) ?>?text=Halo, order: " + data.title;
        document.getElementById('artModal').style.display = 'block';
        document.body.style.overflow = 'hidden'; 
        history.pushState({ modalOpen: true }, "");
    }

    function closeModal() { 
        document.getElementById('artModal').style.display = 'none'; 
        document.getElementById('m-video').pause(); 
        document.getElementById('m-yt-frame').src = "";
        document.body.style.overflow = 'auto'; 
        if (history.state && history.state.modalOpen) { history.back(); }
    }

    window.onpopstate = function(event) {
        if (document.getElementById('artModal').style.display === 'block') {
            document.getElementById('artModal').style.display = 'none'; 
            document.getElementById('m-video').pause(); 
            document.getElementById('m-yt-frame').src = "";
            document.body.style.overflow = 'auto'; 
        }
    };

    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const trigger = document.querySelector('.menu-trigger');
        if (!sidebar.contains(event.target) && !trigger.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>
</body>
</html>