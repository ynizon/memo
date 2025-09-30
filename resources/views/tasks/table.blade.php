<div class="border-bottom py-3 px-3 d-sm-flex align-items-center">

    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">

        @if ($groupId == 0)
            <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotableX" autocomplete="off" @if ($categoryId == 0) checked="" @endif>
            <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-chart="*" data-category="" for="btnradiotableX">{{__("All")}}</label>
            @foreach($categories as $category)
                <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotable{{$loop->index}}" autocomplete="off"  @if ($categoryId == $category->id) checked="" @endif>
                <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-chart="{{$loop->index}}" data-category="{{$category->id}}" for="btnradiotable{{$loop->index}}"><i class="fa {{$category->icon}}"></i></label>
            @endforeach
        @endif
        @if (count($groups) > 0)
            &nbsp;&nbsp;&nbsp;&nbsp;
            <label class="btn btn-white px-3 mb-0 datafiltreCategory" onclick="window.location = '/tasks'">
                <a @if (0 == $groupId) style="font-weight: 700;color: #774dd3;" @endif href="/tasks/"><i class="fa fa-user"></i></a>
            </label>
            @foreach($groups as $group)
                <label class="btn btn-white px-3 mb-0 datafiltreCategory " onclick="window.location = '/tasks?group_id={{$group['id']}}'">
                    <a @if ($group['id'] == $groupId) style="font-weight: 700;color: #774dd3;" @endif
                    href="/tasks/?group_id={{$group['id']}}">{{$group['name']}}</a>
                </label>
            @endforeach
        @endif
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
            <th>
                {{__('Name')}}
            </th>
            <th>
                {{__('Amount')}}
            </th>
            <th>
                {{__('Date')}}
            </th>
            <th>
                {{__('Information')}}
            </th>
        </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>
                        <div class="d-flex">
                            <a href="@if ($task->user_id == Auth::user()->id) /tasks/{{$task->id}}/edit @else /tasks/{{$task->id}} @endif">
                                @if ($task->user_id != Auth::user()->id)
                                    <i class="pad fa fa-group"></i>
                                @else
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                @endif
                                <i class="pad fa fa-list {{$task->category->icon}}"></i>
                            </a>
                            <span class="d-none">Category-{{$task->category->id}}</span>

                            <div class="my-auto">
                                <a href="@if ($task->user_id == Auth::user()->id) /tasks/{{$task->id}}/edit @else /tasks/{{$task->id}} @endif" class="list_task_item">
                                    <h6 class="mb-0 text-sm">
                                        @if ($task->reminder)
                                            <svg height="22" width="22" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="padding-right:5px"
                                                 fill="currentColor" class="cursor-pointers">
                                                <path fill-rule="evenodd"
                                                      d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9a2.25 2.25 0 104.496 0 25.057 25.057 0 01-4.496 0z"
                                                      clip-rule="evenodd" />
                                            </svg>&nbsp;
                                        @endif
                                        {{$task->name}}
                                    </h6>
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
                        {{$task->price}}
                    </td>
                    <td data-sort='YYYYMMDD'>
                        <a href="@if ($task->user_id == Auth::user()->id) /tasks/{{$task->id}}/edit @else /tasks/{{$task->id}} @endif">
                            {{formatDate($task->created_at)}}
                        </a>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <a href="@if ($task->user_id == Auth::user()->id) /tasks/{{$task->id}}/edit @else /tasks/{{$task->id}} @endif">
                            {{$task->information}}
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>{{__("Total page")}}
                    <br/>{{__("Total full")}}
                </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

<br/>
<script src="/assets/js/plugins/datatables.js"></script>
<script src="/assets/js/plugins/datatables-override.js"></script>
<script>
    window.onload = function(e){
        let columnDefs = setColumnDefsAmount(1);
        const dataTableBasic = initializeDataTable("#datatable", 1, columnDefs);

        $('#datatable-search').keyup(function () {
            dataTableBasic.search($(this).val()).draw();
        })

        $('.datafiltreCategory').click(function () {
            dataTableBasic.search('Category-' + $(this).attr('data-category')).draw();
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
