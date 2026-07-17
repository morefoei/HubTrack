<section id="analytics-view" class="view-section">
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: none; padding-bottom: 0;">
                    <h2 class="card-title" style="margin: 0;"><i class="fa-solid fa-chart-line" style="color: var(--primary);"></i> Statistik Input</h2>
                    <div style="display: flex; gap: 0.5rem;">
                        <select id="analyticsMonth" style="padding: 0.5rem; border-radius: 4px; border: 1px solid var(--panel-border); width: auto;">
                            <option value="all">Semua Bulan</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <select id="analyticsYear" style="padding: 0.5rem; border-radius: 4px; border: 1px solid var(--panel-border); width: auto;">
                            <option value="all">Semua Tahun</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                </div>
                <div style="height: 300px; width: 100%;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
            <div class="card">
                <h2 class="card-title"><i class="fa-solid fa-trophy" style="color: #fcd34d;"></i> Top Projects Terbanyak</h2>
                <div id="topProjectsList" style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
                    <!-- List will be populated by JS -->
                </div>
            </div>
        </section>