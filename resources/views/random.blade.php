@extends("layouts.random")

@section("title", "สุ่มรายชื่อสมาชิกสหกรณ์")

@section("content")
    @php
        $remainingCount = count($remaining);
        $drawnCount = count($drawnKeys);
        $hasHistory = count($history) > 0;
        $hasNames = count($names) > 0;
    @endphp

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12">
        <header class="no-print mb-10 text-center">
            <div class="mx-auto mb-5 inline-flex items-center gap-2 clay-chip clay-chip-mint">
                <span class="clay-dot"></span>
                ประชุมใหญ่สามัญ · สหกรณ์
            </div>
            <h1 class="font-display text-3xl font-bold leading-tight text-clay-ink sm:text-5xl">
                สุ่มรายชื่อสมาชิก
            </h1>
        </header>

        <div class="no-print mb-8 flex flex-wrap justify-center gap-3">
            <span class="clay-step clay-step-on"><span class="clay-dot"></span>โหลดรายชื่อ</span>
            <span class="clay-step {{ $hasNames ? "clay-step-on" : "" }}"><span class="clay-dot"></span>สุ่มจำนวน</span>
            <span class="clay-step {{ $hasHistory ? "clay-step-on" : "" }}"><span class="clay-dot"></span>ดูผล / ประวัติ</span>
        </div>

        @if ($errors->any())
            <div role="alert" class="clay-alert clay-alert-error no-print mb-6">
                @foreach ($errors->all() as $error)
                    {{ $error }}@if (!$loop->last)<br>@endif
                @endforeach
            </div>
        @endif

        @if (session("success"))
            <div role="alert" class="clay-alert clay-alert-ok no-print mb-6">
                {{ session("success") }}
            </div>
        @endif

        @unless ($hasNames)
            <section class="clay-panel no-print mb-8 p-6 sm:p-8">
                <div class="mb-6">
                    <h2 class="font-display text-2xl font-semibold text-clay-ink">โหลดรายชื่อ</h2>
                    <p class="mt-1 text-sm text-clay-muted">ไฟล์ Excel คอลัมน์: ลำดับ · รหัสพนักงาน · ชื่อ - นามสกุล</p>
                </div>

                <form action="{{ route("random.upload") }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-clay-ink">เลือกไฟล์ Excel</legend>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls" class="file-input clay-file">
                    </fieldset>

                    @if ($defaultExists)
                        <label class="clay-inset flex cursor-pointer items-start gap-3 p-4">
                            <input type="checkbox" name="use_default" value="1" class="checkbox checkbox-success mt-0.5" checked>
                            <span class="text-sm">
                                <span class="font-medium text-clay-ink">ใช้ไฟล์เริ่มต้น</span>
                                <span class="mt-0.5 block text-clay-muted">{{ $defaultFile }}</span>
                            </span>
                        </label>
                    @endif

                    <button type="submit" class="clay-btn clay-btn-mint">โหลดรายชื่อ</button>
                </form>
            </section>
        @endunless

        @if ($hasNames)
            <section class="clay-panel no-print mb-8 overflow-hidden p-0">
                <div class="border-b-[3px] border-clay-line bg-[#d8efe4] px-6 py-5 sm:px-8">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="font-display text-xl font-semibold text-clay-ink sm:text-2xl">สุ่มจากรายชื่อ</h2>
                            <p class="mt-1 text-sm text-clay-muted">
                                เหลือ {{ $remainingCount }} คน · ทั้งหมด {{ count($names) }} คน
                                @if (session("random_source"))
                                    · {{ session("random_source") }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="clay-chip clay-chip-peach">สุ่มแล้ว {{ $drawnCount }}</span>
                            @if ($hasHistory)
                                <button type="submit" form="clear-history-form" class="clay-btn clay-btn-ghost">
                                    ล้างประวัติการสุ่ม
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($remainingCount > 0)
                    <form action="{{ route("random.pick") }}" method="POST" class="flex flex-col items-stretch gap-5 p-6 sm:flex-row sm:items-end sm:gap-6 sm:p-8">
                        @csrf
                        <fieldset class="fieldset w-full flex-1">
                            <legend class="fieldset-legend text-clay-ink">จำนวนที่ต้องการสุ่ม</legend>
                            <input
                                type="number"
                                name="count"
                                id="count"
                                min="1"
                                max="{{ $remainingCount }}"
                                value="{{ old("count", min(1, $remainingCount)) }}"
                                required
                                class="input input-lg clay-input text-center text-3xl font-bold tracking-wide"
                            >
                            <p class="label text-clay-muted">สูงสุด {{ $remainingCount }} คน (ไม่ซ้ำกับรอบก่อน)</p>
                        </fieldset>

                        <button type="submit" class="clay-btn clay-btn-peach clay-btn-draw sm:mb-6">
                            สุ่มเลย
                        </button>
                    </form>
                @else
                    <div class="p-6 sm:p-8">
                        <div role="alert" class="clay-alert clay-alert-warn">
                            สุ่มครบทุกรายชื่อแล้ว กด “ล้างประวัติการสุ่ม” หากต้องการเริ่มใหม่
                        </div>
                    </div>
                @endif
            </section>

            @if ($hasHistory)
                <section id="results" class="mb-8 space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-display text-xl font-semibold text-clay-ink sm:text-2xl">ประวัติการสุ่ม</h2>
                            <p class="mt-1 text-sm text-clay-muted">
                                {{ count($history) }} รอบ · สุ่มไปแล้ว {{ $drawnCount }} คน
                            </p>
                        </div>
                        <button type="button" onclick="window.print()" class="clay-btn clay-btn-ghost no-print">
                            พิมพ์ประวัติ
                        </button>
                    </div>

                    @foreach ($history as $roundIndex => $round)
                        <article class="clay-panel p-5 sm:p-6 {{ $roundIndex === 0 ? "ring-2 ring-[#f0a878]/50" : "" }}">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="clay-chip {{ $roundIndex === 0 ? "clay-chip-peach" : "clay-chip-sky" }}">
                                        รอบที่ {{ $round["round"] }}
                                    </span>
                                    @if ($roundIndex === 0)
                                        <span class="clay-chip clay-chip-mint">ล่าสุด</span>
                                    @endif
                                    <span class="text-sm text-clay-muted">{{ $round["drawn_at"] }}</span>
                                </div>
                                <span class="text-sm font-semibold text-clay-muted">{{ $round["count"] }} คน</span>
                            </div>

                            <ol class="space-y-3">
                                @foreach ($round["people"] as $i => $person)
                                    <li
                                        @class([
                                            "clay-inset flex items-center gap-4 px-4 py-3 sm:px-5",
                                            "winner-reveal" => $roundIndex === 0,
                                        ])
                                        @if ($roundIndex === 0)
                                            style="animation-delay: {{ min($i * 60, 600) }}ms"
                                        @endif
                                    >
                                        <span class="clay-number {{ $roundIndex === 0 ? "clay-number-hot" : "clay-number-cool" }}">
                                            {{ $i + 1 }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-display text-base font-semibold leading-snug text-clay-ink sm:text-lg">
                                                {{ $person["name"] }}
                                            </p>
                                            <p class="mt-0.5 text-sm text-clay-muted">
                                                ลำดับ {{ $person["seq"] }}
                                                @if ($person["code"] !== "")
                                                    · รหัส {{ $person["code"] }}
                                                @endif
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </article>
                    @endforeach
                </section>
            @endif

            <section class="no-print mb-4">
                <div tabindex="0" class="collapse-arrow collapse clay-panel">
                    <div class="collapse-title font-display text-base font-semibold text-clay-ink">
                        ดูรายชื่อทั้งหมด ({{ count($names) }} คน)
                    </div>
                    <div class="collapse-content space-y-5">
                        <div class="clay-inset p-4">
                            <h3 class="mb-3 font-display text-sm font-semibold text-clay-ink">เปลี่ยนไฟล์รายชื่อ</h3>
                            <form action="{{ route("random.upload") }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf

                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-clay-ink">อัปโหลดไฟล์ใหม่</legend>
                                    <input type="file" name="file" id="file-replace" accept=".xlsx,.xls" class="file-input clay-file">
                                    <p class="label text-clay-muted">การโหลดใหม่จะล้างประวัติการสุ่มด้วย</p>
                                </fieldset>

                                @if ($defaultExists)
                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input type="checkbox" name="use_default" value="1" class="checkbox checkbox-sm checkbox-success mt-0.5">
                                        <span class="text-sm text-clay-ink">
                                            ใช้ไฟล์เริ่มต้น
                                            <span class="block text-clay-muted">{{ $defaultFile }}</span>
                                        </span>
                                    </label>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="clay-btn clay-btn-mint !px-4 !py-2 !text-sm">โหลดไฟล์ใหม่</button>
                                    <button type="submit" form="clear-form" class="clay-btn clay-btn-ghost">ล้างรายชื่อ</button>
                                </div>
                            </form>
                        </div>

                        <div class="clay-inset max-h-80 overflow-auto p-2">
                            <table class="table table-sm">
                                <thead>
                                    <tr class="text-clay-muted">
                                        <th>ลำดับ</th>
                                        <th>รหัสพนักงาน</th>
                                        <th>ชื่อ - นามสกุล</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($names as $person)
                                        @php
                                            $key = ($person["code"] !== "" ? "code:".$person["code"] : "name:".$person["name"]."|".$person["seq"]);
                                            $isDrawn = in_array($key, $drawnKeys, true);
                                        @endphp
                                        <tr class="{{ $isDrawn ? "opacity-45" : "" }}">
                                            <td>{{ $person["seq"] }}</td>
                                            <td>{{ $person["code"] }}</td>
                                            <td class="font-medium">{{ $person["name"] }}</td>
                                            <td>
                                                @if ($isDrawn)
                                                    <span class="clay-chip clay-chip-peach !px-2 !py-0.5 !text-xs">สุ่มแล้ว</span>
                                                @else
                                                    <span class="clay-chip clay-chip-mint !px-2 !py-0.5 !text-xs">รอสุ่ม</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <form id="clear-form" action="{{ route("random.clear") }}" method="POST" class="hidden">@csrf</form>
            <form id="clear-history-form" action="{{ route("random.clearHistory") }}" method="POST" class="hidden">@csrf</form>
        @endif

        <footer class="no-print mt-12 text-center text-xs text-clay-muted/70">
            สุ่มรายชื่อสมาชิกสหกรณ์ · ใช้เฉพาะงานประชุม
        </footer>
    </div>
@endsection

@push("scripts")
<script>
    document.addEventListener("DOMContentLoaded", function () {
        @if ($hasHistory)
            const results = document.getElementById("results");
            if (results) {
                results.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        @endif
    });
</script>
@endpush
