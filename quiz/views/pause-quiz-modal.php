<style>
    /* Dark overlay when paused */
    .pause-modal {
        display: flex;
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1000;
    }

    /* Styling the modal box */
    .pause-content {
        background: #ffffff;
        padding: 30px;
        text-align: center;
        border-radius: 12px;
        width: 350px;
        box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
        transform: scale(0.9);
        transition: transform 0.3s ease-in-out;
    }

    /* Text inside the modal */
    .pause-content p {
        font-size: 1.6rem;
        color: #333;
        font-weight: bold;
        margin-bottom: 10px;
    }

    /* Styling for time remaining text */
    #paused-time {
        font-size: 1.8rem;
        color: #007BFF;
        font-weight: bold;
        display: block;
        margin-top: 5px;
    }

    /* Play button styles */
    .play-btn {
        width: 70px;
        height: 70px;
        cursor: pointer;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    /* Play button hover effect */
    .play-btn:hover {
        transform: scale(1.15);
    }

    /* Hide modal initially */
    .hidden {
        display: none;
    }

</style>

<!-- Pause Modal -->
<div id="pause-modal" class="pause-modal hidden">
    <div class="pause-content">
        <p>⏸ Paused</p>
        <p>Time Remaining: <span id="paused-time"></span></p>
        <img src="<?php echo BASE_URL ?>public/images/quiz/play-buttton.png" id="play-btn" class="play-btn" alt="Play Button">
    </div>
</div>
