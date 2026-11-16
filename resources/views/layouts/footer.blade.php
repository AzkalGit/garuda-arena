<footer id="contact" class="bg-green-900 text-white pt-14 pb-10 mt-16 border-t border-green-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4 pb-10 border-b border-green-800">
            <div class="lg:col-span-2 space-y-3">
                <span class="font-heading text-xl font-semibold">Garuda Arena</span>
                <p class="text-sm text-green-100/70 max-w-sm leading-relaxed">
                    Pusat booking lapangan futsal modern, bersih, dan berstandar profesional. Mainkan hobimu dengan kenyamanan maksimal.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-green-100 mb-4">Jenis Lapangan</h4>
                <ul class="space-y-2.5 text-sm text-green-100/70">
                    <li><a href="{{ route('lapangan.index', ['jenis' => 'interlock']) }}" class="hover:text-white transition-colors">Interlock</a></li>
                    <li><a href="{{ route('lapangan.index', ['jenis' => 'rumput']) }}" class="hover:text-white transition-colors">Rumput Sintetis</a></li>
                    <li><a href="{{ route('lapangan.index', ['jenis' => 'vinyl']) }}" class="hover:text-white transition-colors">Vinyl</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-green-100 mb-4">Hubungi Kami</h4>
                <ul class="space-y-3 text-sm text-green-100/70">
                    <li>Jl. Stadion Olahraga No. 88, Banda Aceh, Indonesia</li>
                    <li>+62 812-3456-7890</li>
                    <li>booking@garudaarena.com</li>
                </ul>
            </div>
        </div>

        <p class="pt-6 text-xs text-green-200/70">Prototipe tugas kuliah – Kelompok 6</p>
    </div>
</footer>