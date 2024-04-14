<div class="border-bottom py-3 px-3 d-sm-flex align-items-center">
    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
        <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotableX" autocomplete="off" @if ($categoryId == 0) checked="" @endif>
        <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-chart="*" data-category="" for="btnradiotableX">{{__("All")}}</label>
        @foreach($categories as $category)
            <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotable{{$loop->index}}" autocomplete="off"  @if ($categoryId == $category->id) checked="" @endif>
            <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-chart="{{$loop->index}}" data-category="{{$category->id}}" for="btnradiotable{{$loop->index}}"><i class="fa {{$category->icon}}"></i></label>
        @endforeach
    </div>
    <div class="input-group w-sm-25 ms-auto py-2 py-lg-0">
        <span class="input-group-text text-body">
        <svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z">
        </path>
        </svg>
        </span>
        <input type="text" id="datatable-search" class="form-control" placeholder="{{__("Search")}}">
    </div>
</div>
<div class="table-responsive">
    <table class="table text-secondary text-center" id="datatable">
        <thead class="bg-gray-100">
        <tr>
            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                {{__('Name')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Amount')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Date')}}
            </th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2 d-none d-md-table-cell">
                {{__('Information')}}</th>
        </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>
                        <div class="d-flex px-2">
                            <div class="rounded-circle bg-gray-100 me-2 my-2">
                                <a href="/tasks/{{$task->id}}/edit">
                                    <i class="pad fa fa-list {{$task->category->icon}}"></i>
                                </a>
                                <span class="d-none">Category-{{$task->category->id}}</span>
                            </div>
                            <div class="my-auto">
                                <a href="/tasks/{{$task->id}}/edit" class="list_task_item">
                                    <h6 class="mb-0 text-sm">{{$task->name}}</h6>
                                </a>
                                @if (count($task->attachments) > 0)
                                    <ul class="docs">
                                    @foreach ($task->attachments as $attachment)
                                            <li><a href="/attachments/{{$attachment->id}}" target="_blank">
                                                    <i class="fa pad fa-paperclip"></i>{{$attachment->name}}
                                                </a>
                                            </li>
                                    @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-weight-normal mb-0">
                            <a href="/tasks/{{$task->id}}/edit">
                                @if ($task->price > 0){{$task->price}} €@endif
                            </a>
                        </p>
                    </td>
                    <td>
                        <span class="text-sm font-weight-normal">
                             <a href="/tasks/{{$task->id}}/edit">
                                {{formatDate($task->created_at)}}
                             </a>
                        </span>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <span class="text-sm font-weight-normal">
                             <a href="/tasks/{{$task->id}}/edit">
                                {{$task->information}}
                             </a>
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script src="/assets/js/plugins/datatables.js"></script>
<script>
    window.onload = function(e){
        const dataTableBasic = new simpleDatatables.DataTable("#datatable", {
            searchable: false,
            fixedHeight: true,
            bLengthChange: false,
            paging: true,
            showNEntries: false,
            perPage: 10,
        });

        $('#datatable-search').keyup(function () {
            dataTableBasic.search($(this).val()).draw();
        })

        $('.datafiltreCategory').click(function () {
            dataTableBasic.search('Category-' + $(this).attr('data-category'));
            if (chart){
                let chartColumn = $(this).attr('data-chart');
                chart.data.datasets.forEach(function(ds) {
                    ds.hidden = !(chartColumn === '*');
                });
                if (chartColumn !== '*'){
                    chart.data.datasets[chartColumn].hidden = false;
                }

                chart.update();
            }
        })

        @if ($categoryId > 0)
            try {
                $("[data-category='{{$categoryId}}']").click();
            } catch(Exception){
              //Nothing
            }
        @endif
    };
</script>
