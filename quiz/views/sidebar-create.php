<style>
    .nav-create {
        border-bottom: white 6px solid !important;
    }

    /* Sidebar styling */
    .quiz-container {
        display: flex;
        min-height: 70vh;
    }

    .sidebar {
        width: 250px;
        background: #f4f4f4;
        padding: 20px;
        border-right: 2px solid #ddd;
    }

    .sidebar a {
        display: block;
        padding: 10px;
        margin: 5px 0;
        text-decoration: none;
        color: black;
        font-weight: bold;
        border-radius: 5px;
        transition: background 0.3s ease, color 0.3s ease;
        pointer-events: none;  /* Disable all interactions initially */
        opacity: 0.5;  /* Make links faded */
    }

    /* Hover effect */
    .sidebar a:hover {
        background: #e0f0ff; /* Light blue */
        color: #0056b3; /* Darker blue text */
    }

    /* Active link */
    .sidebar a.active {
        background: #007BFF;
        color: white;
        pointer-events: auto;  /* Enable interaction when active */
        opacity: 1;  /* Remove fade effect */
    }

    /* Content area */
    .content {
        flex: 1;
        padding: 20px;
    }
</style>

<div class="sidebar">
    <a href="?create=BasicInfo"
       class="<?php echo (!isset($_GET['create']) || $_GET['create'] == 'BasicInfo') ? 'active' : ''; ?>">Basic Info</a>
    <a href="?create=Data"
       class="<?php echo (isset($_GET['create']) && $_GET['create'] == 'Data' && $_SESSION['basic_info_completed']) ? 'active' : ''; ?>">Data</a>
    <a href="?create=Advanced"
       class="<?php echo (isset($_GET['create']) && $_GET['create'] == 'Advanced' && $_SESSION['basic_info_completed']) ? 'active' : ''; ?>">Advanced</a>
    <a href="?create=Contribute"
       class="<?php echo (isset($_GET['create']) && $_GET['create'] == 'Contribute' && $_SESSION['basic_info_completed']) ? 'active' : ''; ?>">Contribute</a>
</div>
