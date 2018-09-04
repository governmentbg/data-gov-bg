<div
    class="
        form-group
        row
        {{ isset($result->errors->tags) ? 'has-error' : '' }}
    "
>
    <label
        for="tags"
        class="col-sm-3 col-xs-12 col-form-label"
    >{{ utrans('custom.labels') .':' }}</label>
    <div class="col-sm-9 example">
        <input
            name="tags"
            class="input-border-r-12 form-control"
            data-role="tagsinput"
            value="
                @if (empty(old('tags')))
                    @if (isset($model))
                        @php
                            $tags = '';

                            if (!empty($model)):
                                foreach ($model as $mod):
                                    $tags = $tags .','. $mod->name;
                                endforeach;
                            endif
                        @endphp
                        {{ $tags }}
                    @endif
                @else
                    {{ old('tags') }}
                @endif
            "
        >
        @if (isset($result->errors->tags))
            <span class="error">{{ $result->errors->tags[0] }}</span>
        @endif
    </div>
</div>
