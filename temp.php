<?php

// Original list with duplicate items
$Lifestyle = [
        "Video Games", "PC Gaming", "Console Gaming", "Mobile Gaming", "Cloud Gaming",
        "Game Streaming", "Esports", "Competitive Gaming", "Indie Games", "AAA Games",
        "RPG Games", "FPS Games", "Battle Royale", "MOBA Games", "MMORPG",
        "Sandbox Games", "Survival Games", "Horror Games", "Adventure Games", "Puzzle Games",
        "Strategy Games", "Real-Time Strategy (RTS)", "Turn-Based Strategy", "Racing Games",
        "Sports Games", "Fighting Games", "Platformer Games", "Metroidvania", "Rogue-like Games",
        "Simulation Games", "City-Building Games", "Tycoon Games", "Card Games", "Board Games",
        "Gacha Games", "Open-World Games", "Multiplayer Games", "Single-Player Games",
        "Co-Op Games", "Online Gaming", "LAN Gaming", "Virtual Reality Gaming", "Augmented Reality Gaming",
        "Retro Gaming", "Classic Arcade Games", "Emulators", "Game Development", "Game Design",
        "Game Programming", "Game Art", "Game Engines", "Unreal Engine", "Unity", "Godot",
        "Pixel Art Games", "2D Games", "3D Games", "VR Headsets", "Gaming Monitors",
        "Gaming Keyboards", "Gaming Mice", "Gaming Controllers", "Gaming Laptops",
        "Gaming PCs", "Next-Gen Consoles", "PlayStation", "Xbox", "Nintendo Switch",
        "Handheld Consoles", "Steam Deck", "Gaming Accessories", "Gaming Headsets",
        "Gaming Chairs", "Gaming Communities", "Speedrunning", "Twitch Streaming",
        "YouTube Gaming", "Gaming Podcasts", "Gaming News", "Gaming Reviews", "Game Guides",
        "Walkthroughs", "Game Mechanics", "Loot Boxes", "Microtransactions", "In-Game Purchases",
        "DLC (Downloadable Content)", "Expansion Packs", "Game Mods", "Modding Community",
        "Skins and Cosmetics", "Battle Pass", "Seasonal Events", "Game Updates", "Patch Notes",
        "Early Access Games", "Game Betas", "Pre-Orders", "Game Collectibles", "Physical Copies",
        "Digital Game Stores", "Steam", "Epic Games Store", "GOG", "Itch.io",
        "Game Subscription Services", "Xbox Game Pass", "PlayStation Plus", "Nintendo eShop",
        "Online Multiplayer", "Voice Chat", "Discord Gaming", "LFG (Looking for Group)",
        "Gaming Tournaments", "Esports Teams", "Esports Leagues", "Fighting Game Tournaments",
        "Speedrun Competitions", "LAN Parties", "Game Jams", "Indie Game Showcases",
        "Crowdfunded Games", "Kickstarter Games", "Horror Game Streaming", "Gaming Nostalgia",
        "Pixel Graphics", "8-Bit Games", "16-Bit Games", "AAA Studios", "Indie Developers",
        "Game Soundtracks", "OSTs (Original Soundtracks)", "Game Music", "Game Voice Acting",
        "Game Storytelling", "Gaming Lore", "Gaming Easter Eggs", "Game Theory",
        "Cross-Platform Gaming", "Backward Compatibility", "Remastered Games", "Game Ports",
        "Gaming Achievements", "Trophy Hunting", "Leaderboard Rankings", "Speedrun Records",
        "Gaming Hardware", "GPU Performance", "Ray Tracing", "Gaming Industry Trends",
        "Game Monetization", "Freemium Games", "Game Advertising", "In-Game Events",
        "Metaverse Gaming", "Blockchain Games", "NFT Gaming", "Play-to-Earn Games",
        "Gamer Culture", "Gaming Merchandise", "Gaming Apparel", "Game Cosplay",
        "Virtual Economy", "Game Collecting", "Retro Game Consoles", "Game Preservation",
        "Gaming Documentaries", "Video Game Adaptations", "Gaming Books", "Esports Sponsorships",
        "Gaming Influencers", "Gaming Content Creators", "Twitch Partners", "YouTube Gaming Creators",
        "Gaming Memes", "Gaming Forums", "Reddit Gaming", "Gaming Subreddits",
        "Gaming Controversies", "Game Delays", "Crunch Culture", "Game Censorship",
        "Video Game Ratings", "PEGI Ratings", "ESRB Ratings", "Parental Controls",
        "Gaming Addiction", "Healthy Gaming Habits", "Gaming and Mental Health",
        "Accessibility in Gaming", "Inclusive Game Design", "Women in Gaming",
        "LGBTQ+ Representation in Gaming", "Diversity in Gaming", "Video Game History",
        "Gaming Museums", "Classic Game Developers", "Famous Game Franchises", "Gamer Stereotypes",
        "Gaming Nostalgia", "Game Physics", "AI in Games", "Procedural Generation",
        "Virtual Economies", "MMO Economies", "Game Hacking", "Cheating in Games",
        "Game Glitches", "Speedrun Glitches", "Gaming Conventions", "E3 Expo",
        "Tokyo Game Show", "Gamescom", "PAX Gaming Convention", "BlizzCon", "Minecon",
        "Fan-made Games", "Game Remakes", "Gaming Parodies", "Gaming Satire",
        "Gaming Fan Art", "Gaming Fandoms", "Game Theories", "Alternate Endings",
        "Branching Narratives", "Moral Choices in Games", "Dynamic Storytelling",
        "Adaptive AI in Games", "Emergent Gameplay", "Gaming Guilds", "In-Game Clans",
        "Guild Management", "Game Coaching", "Esports Scholarships", "Professional Gaming",
        "Streaming Careers", "Gaming Sponsorships", "Gaming Merchandise Sales",
        "Physical vs Digital Games", "Collector’s Editions", "Limited Run Games",
        "Video Game Documentaries", "Gaming Nostalgia Channels", "Gaming YouTubers",
        "VR Arcades", "Classic Gaming Cafés", "Esports Betting", "Gaming Laws and Regulations",
        "Censorship in Gaming", "Banned Games", "Cultural Representation in Games",
        "Game Awards", "Game of the Year", "Best Indie Game", "Gaming Hall of Fame",
        "Gaming Trivia", "Game AI Evolution", "Procedural Storytelling", "Game Mechanics Innovation",
        "Experimental Games", "Interactive Fiction", "Gaming and Education",
        "Serious Games", "Gamification", "VR Training Simulations", "AR Learning Games",
        "Gaming Charity Events", "Gaming Marathons", "Extra Life Charity", "Speedrun Charity Events",
        "Esports Philanthropy", "Gaming and Disability Advocacy", "Accessible Controllers",
        "Custom Game Controllers", "Haptic Feedback in Gaming", "Motion Controls",
        "Gaming and Music Crossovers", "Video Game Concerts", "Live-Orchestra Game Music",
        "Gaming-Inspired Art", "Cyberpunk Gaming", "Steampunk Games", "Sci-Fi RPGs",
        "Fantasy RPGs", "Cybersecurity in Gaming", "Online Safety in Gaming",
        "Parental Supervision in Gaming", "Gaming AI Bots", "Auto-Battlers", "Gaming Social Platforms",
        "NFT-Based Games", "Game Rental Services", "Game Subscription Models", "Gaming Investment",
        "Gaming and Cryptocurrency", "Game Developers' Union", "Gaming Journalism", "Game Criticism",
        "Virtual Concerts in Games", "Gaming and Film Crossovers", "Gaming in Pop Culture",
        "Esports Arenas", "Professional Gaming Leagues", "Gaming Lifestyle", "Streaming Setups",
        "Gaming Challenges", "Video Game Documentaries", "Gaming Legacy"
];

// Remove duplicates using array_unique()
$unique_reviews = array_unique($Lifestyle);

// Output the result
echo '"Lifestyle" => [';
foreach ($unique_reviews as $review) {
    echo '"' . $review . '", ';
}
// Removing the last comma and space
echo '];';

?>
