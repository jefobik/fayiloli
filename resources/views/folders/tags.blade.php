<div
    class="table-responsive overflow-y-auto max-h-[65vh] rounded-b-lg scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
    <table class="table table-hover align-middle mb-0 w-full" aria-label="Tags by category">
        <thead
            class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800 shadow-sm border-b border-slate-200 dark:border-slate-700">
            <tr>
                <th scope="col" class="ps-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Category
                </th>
                <th scope="col" class="py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tags</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr
                    class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors border-b border-slate-100 dark:border-slate-800 last:border-0">
                    <td class="ps-4 fw-semibold py-3" data-label="Category">
                        <i class="fa fa-arrows mt-1 text-slate-400 me-2"></i>{{ $category->name }}
                    </td>
                    <td class="py-3" data-label="Tags">
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($category->tags as $tag)
                                <span class="badge text-info"
                                    style="background-color:{{ $tag->background_color ?? '#6c757d' }};color:{{ $tag->foreground_color ?? '#fff' }} !important;border:1px solid rgba(0,0,0,0.1)">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>