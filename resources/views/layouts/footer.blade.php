<footer id="contact" class="footer">
    <div class="wadah">
        <div class="footer-grid">
            <div class="footer-tentang">
                <span class="footer-merek">Garuda Arena</span>
                <p class="footer-teks">
                    Pusat booking lapangan futsal modern, bersih, dan berstandar profesional. Mainkan hobimu dengan kenyamanan maksimal.
                </p>
            </div>

            <div>
                <h4 class="footer-judul">Jenis Lapangan</h4>
                <ul class="footer-daftar">
                    <li><a href="{{ route('lapangan.index', ['jenis' => 'interlock']) }}" class="footer-link">Interlock</a></li>
                    <li><a href="{{ route('lapangan.index', ['jenis' => 'rumput']) }}" class="footer-link">Rumput Sintetis</a></li>
                    <li><a href="{{ route('lapangan.index', ['jenis' => 'vinyl']) }}" class="footer-link">Vinyl</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-judul">Hubungi Kami</h4>
                <ul class="footer-daftar longgar">
                    <li>Jl. Stadion Olahraga No. 88, Banda Aceh, Indonesia</li>
                    <li>+62 812-3456-7890</li>
                    <li>booking@garudaarena.com</li>
                </ul>
            </div>
        </div>

        <p class="footer-catatan">Prototipe tugas kuliah – Kelompok 6</p>
    </div>
</footer>