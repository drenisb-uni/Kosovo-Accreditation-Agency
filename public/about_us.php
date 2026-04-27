<?php 
include_once '../includes/header.php'; 

$goals = [
    ["text" => "Të promovojë, përmirësoj dhe rrisë cilësinë e arsimit të lartë", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>'],
    ["text" => "Të rrisë transparencën dhe llogaridhënien në sistem", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>'],
    ["text" => "Të përmirësojë cilësinë e studimeve në institucione", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>'],
    ["text" => "Të inkurajoj përmbajtje inovative në arsimin e lartë", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>'],
    ["text" => "Krahasueshmëria e kualifikimeve me ato ndërkombëtare", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2 2 2 0 012 2v.65m2.875-2.015a11.05 11.05 0 01-1.283 1.447m1.283-1.447A11 11 0 1020.9 13.11" /></svg>'],
    ["text" => "Integrimi në Zonën Evropiane të Arsimit të Lartë", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>']
];

$values = [
    ["title" => "Pavarësia", "desc" => "Vendimet e AKA-së merren në mënyrë të pavarur dhe të arsyetuar.", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>'],
    ["title" => "Transparenca", "desc" => "AKA udhëhiqet nga parimet e përgjegjësisë dhe llogaridhënies publike.", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>'],
    ["title" => "Besueshmëria", "desc" => "AKA organizon procese kredibile ku komuniteti akademik beson.", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>'],
    ["title" => "Profesionalizmi", "desc" => "AKA aplikon standarde të larta profesionale evropiane.", "icon" => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>']
];
?>

<div class="about-container">
    <main class="about-content">
        
        <section class="goals-section">
            <h2 class="section-title">Qëllimet tona</h2>
            <div class="goals-grid">
                <?php foreach ($goals as $goal): ?>
                    <div class="goal-card">
                        <div class="icon-wrapper">
                            <?php echo $goal['icon']; ?>
                        </div>
                        <p class="goal-text"><?php echo $goal['text']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="values-section">
            <div class="values-header">
                <h2 class="values-title">Vlerat Institucionale</h2>
                <div class="divider"></div>
            </div>
            <div class="values-grid">
                <?php foreach ($values as $v): ?>
                    <div class="value-item">
                        <div class="value-icon-circle">
                            <?php echo $v['icon']; ?>
                        </div>
                        <h3 class="value-name"><?php echo $v['title']; ?></h3>
                        <p class="value-desc"><?php echo $v['desc']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
</div>

<?php include_once '../includes/footer.php'; ?>