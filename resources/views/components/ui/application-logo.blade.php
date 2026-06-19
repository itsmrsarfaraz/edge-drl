<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="100%" height="100%">
                        <defs>
                            <radialGradient id="logo-glow" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#1e1b4b" stop-opacity="0.6"/>
                            <stop offset="100%" stop-color="#020617" stop-opacity="0"/>
                            </radialGradient>

                            <linearGradient id="node-violet" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#8b5cf6"/>
                            <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>

                            <linearGradient id="node-danger" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#ff4d5a"/>
                            <stop offset="100%" stop-color="#dc3545"/>
                            </linearGradient>

                            <linearGradient id="vector-flow" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.8"/>
                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0.1"/>
                            </linearGradient>

                            <filter id="core-blur" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="8" result="blur"/>
                            <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                            </filter>
                        </defs>

                        <circle cx="400" cy="400" r="350" fill="url(#logo-glow)"/>

                        <g stroke="#1e293b" stroke-width="3" opacity="0.6" fill="none">
                            <circle cx="400" cy="400" r="280" stroke-dasharray="14,14"/>
                            <circle cx="400" cy="400" r="180"/>
                            <path d="M 120 400 L 680 400 M 400 120 L 400 680 M 202 202 L 598 598 M 202 598 L 598 202"/>
                        </g>

                        <g fill="none" stroke-width="5" stroke-linecap="round">
                            <path d="M 220,300 C 260,200 340,180 400,220" stroke="url(#node-violet)" stroke-dasharray="4,200" stroke-dashoffset="50"/>
                            <path d="M 580,500 C 540,600 460,620 400,580" stroke="url(#node-violet)"/>
                            <path d="M 400,220 C 480,200 550,280 580,350" stroke="url(#node-danger)" stroke-width="6" filter="url(#core-blur)"/>
                            <path d="M 220,500 C 180,420 180,350 220,300" stroke="#334155"/>
                        </g>

                        <g fill="none" stroke="url(#vector-flow)" stroke-width="4">
                            <path d="M 400,220 L 400,340"/>
                            <path d="M 220,300 L 320,370"/>
                            <path d="M 580,350 L 480,400"/>
                            <path d="M 220,500 L 320,430"/>
                            <path d="M 580,500 L 480,430"/>
                        </g>

                        <g filter="url(#core-blur)">
                            <circle cx="220" cy="300" r="24" fill="url(#node-violet)"/>
                            <circle cx="580" cy="350" r="24" fill="url(#node-violet)"/>
                            <circle cx="220" cy="500" r="24" fill="#334155"/>
                            <circle cx="580" cy="500" r="24" fill="url(#node-violet)"/>
                            <circle cx="400" cy="580" r="28" fill="url(#node-violet)"/>

                            <circle cx="400" cy="220" r="32" fill="url(#node-danger)"/>
                            <circle cx="400" cy="220" r="42" fill="none" stroke="#ff4d5a" stroke-width="2" opacity="0.5"/>
                        </g>

                        <g>
                            <rect x="310" y="340" width="180" height="120" rx="20" fill="#0f172a" fill-opacity="0.9" stroke="#334155" stroke-width="3"/>
                            <g fill="#94a3b8" opacity="0.8">
                            <circle cx="350" cy="380" r="6"/>
                            <circle cx="350" cy="420" r="6"/>
                            <circle cx="400" cy="400" r="9" fill="#ff4d5a"/>
                            <circle cx="450" cy="380" r="6"/>
                            <circle cx="450" cy="420" r="6"/>
                            </g>
                            <g stroke="#475569" stroke-width="2">
                            <line x1="350" y1="380" x2="400" y2="400"/>
                            <line x1="350" y1="420" x2="400" y2="400"/>
                            <line x1="400" y1="400" x2="450" y2="380"/>
                            <line x1="400" y1="400" x2="450" y2="420"/>
                            </g>
                        </g>

                        <g fill="#ffffff" opacity="0.9">
                            <circle cx="400" cy="220" r="10"/>
                            <circle cx="220" cy="300" r="6"/>
                            <circle cx="580" cy="350" r="6"/>
                            <circle cx="580" cy="500" r="6"/>
                            <circle cx="400" cy="580" r="8"/>
                        </g>
                    </svg>