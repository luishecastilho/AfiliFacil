const ITEMS = ['Uma nota por vendedor', 'Em 1 clique', 'NFS-e automática', 'Rápido e fácil'];

function MarqueeContent() {
    return (
        <>
            {ITEMS.map((item, index) => (
                <span key={item} className="flex shrink-0 items-center gap-8">
                    <span
                        className={`font-display text-3xl font-bold uppercase tracking-tight sm:text-4xl ${
                            index % 2 === 0 ? 'text-white' : 'text-stroke text-white/60'
                        }`}
                    >
                        {item}
                    </span>
                    <span className="text-2xl text-[#EE4D2D]">✦</span>
                </span>
            ))}
        </>
    );
}

export function Marquee() {
    return (
        <div aria-hidden="true" className="overflow-hidden border-y border-white/10 bg-[#0a0a0a] py-6">
            <div className="flex w-max gap-8 animate-marquee">
                <div className="flex shrink-0 items-center gap-8">
                    <MarqueeContent />
                </div>
                <div className="flex shrink-0 items-center gap-8">
                    <MarqueeContent />
                </div>
            </div>
        </div>
    );
}
