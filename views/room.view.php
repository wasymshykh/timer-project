<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<div class="page-body <?=$dafault_section?>">
    
    <div class="page-title room-title">
        <h2><?=$room['room_name']?></h2>
    </div>

    <div class="room-page">
        <div class="page-sub-title room-status">
            <h3><?php if (!$timer_configured):?>Waiting for host... <?php else: ?> <?=$room['room_status'] === 'A'?'Work':'Pause'?><?php endif;?></h3>
        </div>
        <div class="page-counter">
            <h1><?php if (!empty($room['room_stop_start'])): ?> 
                    <?=$room['room_stop_start']?> 
                <?php else: ?> 
                    <?php if (!empty($room['room_pause_start'])): ?>
                        <?=$room['room_pause_start']?>
                    <?php endif; ?>
                <?php endif; ?>
            </h1>
            <p>Round: <strong id="r-round"><?=$room['room_round']?></strong></p>
        </div>
        <?php if ($member['member_type'] === 'H'): ?>
        <div class="page-timer-button">
            <button class="btn-submit pause-btn"><?=(!empty($room['room_stop_start'])) ? 'Resume' : 'Stop'?></button>
            <div class="timer-reset">
                <button class="reset-btn reset-btn">Reset</button>
            </div>
            <div class="timer-config">
                <button class="config-btn config-change"><i class="fa fa-cog"></i></button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="room-finished">
        <?php if ($member['member_type'] === 'M'): ?>
        <div class="page-sub-title">
            <h3>Waiting for the host...</h3>
        </div>
        <?php endif; ?>
        <div class="page-counter-finished">
            <h1>Finished</h1>
        </div>
        <?php if ($member['member_type'] === 'H'): ?>
        <div class="page-timer-button">
            <button class="btn-submit config-change">Back to configuration</button>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($member['member_type'] === 'H'): ?>
    <div class="room-config">
        
        <div class="page-input">
            <div class="page-input-td">
                <label for="work-time-1">Work Time</label>
            </div>
            <div class="page-input-td time-td">
                <input type="text" id="work-time-1" value="<?=$room['work_minute']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">:</div>
                <input type="text" id="work-time-2" value="<?=$room['work_seconds']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">min</div>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label for="pause-time-1">Pause</label>
            </div>
            <div class="page-input-td time-td">
                <input type="text" id="pause-time-1" value="<?=$room['pause_minute']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">:</div>
                <input type="text" id="pause-time-2" value="<?=$room['pause_seconds']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">min</div>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label for="sound">Sound</label>
            </div>
            <div class="page-input-td">
                <select id="sound">
                    <option value="bip bip bip" <?=$room['room_sound_type']==='bip bip bip'?'selected':''?>>Bip Bip Bip</option>
                    <option value="buzzer" <?=$room['room_sound_type']==='buzzer'?'selected':''?>>Buzzer</option>
                    <option value="dring" <?=$room['room_sound_type']==='dring'?'selected':''?>>Dring</option>
                </select>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label for="round-number">Round</label>
            </div>
            <div class="page-input-td">
                <input type="number" id="round-number" value="<?=$room['room_round_limit']??'1'?>">
            </div>
        </div>

        <div class="page-input-submit">
            <button type="button" class="btn-submit btn-start-timer">Start Timer</button>
        </div>

    </div>
    <?php endif; ?>

    <div class="room-connected">
        <p><span id="members">0</span> members connected</p>
        
        <div class="view-all">
            <button type="button" class="members-btn">view all</button>
    
            <div class="room-connected-drop">
                <ul class="no">
                    <li></li>
                </ul>
            </div>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.1.1/howler.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.33/moment-timezone-with-data-10-year-range.min.js" integrity="sha512-48hjVXIRd5d3sGKDfphGflqnxq91J14nwVO5Q6dHhK66n9XjP4zg0YR8IJRNZSJAVCrjjTU4fgpQgXeyx2lHNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    toastr.options.closeButton = true;

    let members_online = [];
    let room_config = {
        sound: '<?=$room['room_sound_type']??''?>',
        work_time: '<?=$room['room_work_time']??''?>',
        pause_time: '<?=$room['room_pause_time']??''?>',
        round: <?=$room['room_round']?>,
        round_limit: <?=$room['room_round_limit']?>,
        configure_date: '<?=$room['room_configure_date']??''?>',
        work_end: '<?=$room['room_work_end_date']??''?>',
        pause_start: '<?=$room['room_pause_start']??''?>',
        pause_end: '<?=$room['room_pause_end']??''?>',
        room_status: '<?=$room['room_status']??''?>',
        stop_start: '<?=$room['room_stop_start']??''?>'
    };
    let finished = false;
    let awaiting = <?=$timer_configured ? 'false' : 'true' ?>;
    let paused = room_config.room_status == 'P' ? true : false;
    let stopped = <?=(!empty($room['room_stop_start'])) ? 'true' : 'false'?>;

    function apply_configure_difference (changes, round_pause = false) {
        if (JSON.stringify(changes) != JSON.stringify(room_config)) {
            room_config = changes;
            finished = false;
            awaiting = false;
            g_round_pause = round_pause;
            $('.pause-btn').text('Stop');
            handle_change_status(g_round_pause);
            handle_timer_stop(false, null);
            $('#r-round').text(room_config.round);
        }
    }

    function validate_field (data) {
        if (data == undefined || data == "" || data.trim() == "") {
            return {status: false, message: 'Field cannot be empty'};
        }
        return {status: true};
    }

    $('.btn-start-timer').on('click', (e) => {
        
        let el_work_time_1 = $('#work-time-1');
        let el_work_time_2 = $('#work-time-2');
        let el_pause_time_1 = $('#pause-time-1');
        let el_pause_time_2 = $('#pause-time-2');
        let el_sound = $('#sound');
        let el_round = $('#round-number');
        
        let valid = true;
        
        var r = validate_field(el_work_time_1.val());
        if (!r.status) {
            valid = false;
            toastr.error('Work time minutes field cannot be empty')
        }
        var r = validate_field(el_work_time_2.val());
        if (!r.status) {
            valid = false;
            toastr.error('Work time seconds field cannot be empty')
        }
        var r = validate_field(el_pause_time_1.val());
        if (!r.status) {
            valid = false;
            toastr.error('Pause time minutes field cannot be empty')
        }
        var r = validate_field(el_pause_time_2.val());
        if (!r.status) {
            valid = false;
            toastr.error('Pause time seconds field cannot be empty')
        }
        var r = validate_field(el_sound.val());
        if (!r.status) {
            valid = false;
            toastr.error('Select the sound')
        }
        var r = validate_field(el_round.val());
        if (!r.status) {
            valid = false;
            toastr.error('Enter the round number')
        }

        if (valid) {
            
            $.ajax({
                url: '<?=URL?>/api/room.php?config=<?=$room['room_id']?>',
                method: 'POST',
                data: {
                    work_minute: el_work_time_1.val(), work_seconds: el_work_time_2.val(),
                    pause_minute: el_pause_time_1.val(), pause_seconds: el_pause_time_2.val(), 
                    sound: el_sound.val(), round: el_round.val()
                },
                success: (data, status) => {
                    data = JSON.parse(data);
                    if (data.status == 200) {
                        data = data.data;
                        var changes = { sound: data.sound, work_time: data.work_time, pause_time: data.pause_time, round: 1, round_limit: data.round, configure_date: data.configure_date, work_end: data.work_end, pause_start: data.pause_start, pause_end: data.pause_end, room_status: 'A', stop_start: null };
                        apply_configure_difference(changes);
                        $('.page-body').removeClass('config').addClass('room');
                    }
                }
            });

        }

    });

    let sound_path = {
        'bip bip bip': '<?=URL?>/assets/sound/bip bip bip.wav',
        'buzzer': '<?=URL?>/assets/sound/gilfoyle_alert.mp3',
        'dring': '<?=URL?>/assets/sound/bip bip bip.wav'
    }

    function handle_change_status (isPaused) {
        paused = isPaused;
        room_config.room_status = paused ? 'P' : 'A';
        room_config.pause_start = g_minutes+":"+g_seconds;
        $('.room-status h3').text(paused ? 'Pause' : 'Work');
        
        make_sound();
    }

    function make_sound () {
        var sound = new Howl({
          src: [sound_path[room_config.sound]],
          volume: 0.5
        });
        sound.play()
    }

    
    function handle_timer_stop (isStopped, stop_start) {
        stopped = isStopped;
        room_config.stop_start = stop_start;
        if (room_config.stop_start != '' && room_config.stop_start != null) {
            $('.page-counter h1').text(room_config.stop_start)
        }
        $('.pause-btn').text(stopped ? 'Resume' : 'Stop');
    }

    $('.pause-btn').on('click', (e) => {
        
        let parm = stopped ? 'resume' : 'pause';
        $.ajax({
            url: '<?=URL?>/api/room.php?status=<?=$room['room_id']?>&'+parm+'=true',
            method: 'POST',
            data: { at_minute: g_minutes, at_seconds: g_seconds },
            success: (data, status) => {
                data = JSON.parse(data);
                if (data.status === 200) {
                    data = data.data;
                    room_config.work_end = data.work_end;
                    room_config.pause_end = data.pause_end;
                    handle_timer_stop(!stopped, data.stop_start);
                }
                
            }
        });

    });

    $('.reset-btn').on('click', (e) => {

        $.ajax({
            'url': '<?=URL?>/api/room.php?reset=<?=$room['room_id']?>',
            'method': 'GET',
            success: (data, status) => {
                data = JSON.parse(data);
                if (data.status == 200) {
                    data = data.data;
                    var changes = { sound: data.sound, work_time: data.work_time, pause_time: data.pause_time, round: data.round, round_limit: data.room_round_limit, configure_date: data.configure_date, work_end: data.work_end, pause_start: data.pause_start, pause_end: data.pause_end, room_status: data.room_status, stop_start: null };
                    
                    g_round_pause = false;
                    if (changes.room_status === 'P') {
                        g_round_pause = true;
                    }
                    apply_configure_difference(changes, g_round_pause);
                }
            }
        });

    });

    $('.members-btn').on('click', (e) => {
        $('.room-connected-drop').toggleClass('active');
    });

    $('.config-change').on('click', (e) => {
        $('.page-body').removeClass('room').removeClass('finished').addClass('config');
    });

    $(document).on('click', (e) => {
        let t = $(e.target);
        if (!t.hasClass('members-btn') && !t.hasClass('no') && !t.parent().hasClass('no')) {
            let rcd = $('.room-connected-drop');
            if (rcd.hasClass('active')) {
                rcd.removeClass('active');
            }
        }
    });

    function apply_online_difference (members) {
        if (JSON.stringify(members) != JSON.stringify(members_online)) {
            members_online = members;
            $('#members').text(members_online.length);

            $('.room-connected-drop ul li').remove();
            members_online.forEach(member => {
                $('.room-connected-drop ul').append(`<li>${member}</li>`);
            })
        }
    }

    function apply_change_difference (changes) {
        if (JSON.stringify(changes) != JSON.stringify(room_config)) {
            
            change_status = false;

            if (changes.configure_date == null) { return }

            if (changes.work_end != room_config.work_end) {
                if ($('.page-body').hasClass('finished')) {
                    $('.page-body').removeClass('finished').addClass('room');
                    finished = false;
                }

                if (awaiting) { awaiting = false; change_status = true; }
            }

            if (changes.round != room_config.round) {
                $('#r-round').text(changes.round);
            }

            if (changes.stop_start != room_config.stop_start) {

                if (changes.stop_start != null && changes.stop_start != '') {
                    stopped = true;
                } else {
                    stopped = false;
                }
                handle_timer_stop(stopped, changes.stop_start);

            }

            if (changes.sound != room_config.sound) {
                room_config.sound = changes.sound;
            }

            if (change_status || (changes.room_status != room_config.room_status)) {
                let isPaused = false;
                if (changes.room_status == 'P') { isPaused = true; }
                handle_change_status(isPaused);
            }

            room_config = changes;
        }
    }

    function get_room_info () {
        $.ajax({
            url: '<?=URL?>/api/room.php?info=<?=$room['room_id']?>',
            method: 'GET',
            timeout: 2000,
            success: (data, status) => {
                data = JSON.parse(data);

                if (data.status == 200) {
                    let check_online = [];
                    data.data.members.forEach((member) => {
                        if (member.online) {
                            check_online.push(member.member_name);
                        }
                    })
                    apply_online_difference(check_online);

                    config = data.data.config;
                    var changes = { sound: config.room_sound_type, work_time: config.room_work_time, pause_time: config.room_pause_time, round: config.room_round, round_limit: config.room_round_limit, configure_date: config.room_configure, work_end: config.work_end, pause_start: config.pause_start, pause_end: config.pause_end, room_status: config.room_status, stop_start: config.stop_start };
                    apply_change_difference(changes);

                }
            }
        });
    }
    setInterval(() => {
        get_room_info();
    }, 2000);

    get_room_info();

    let g_minutes = '<?=$room['pause_minutes_at']??'00'?>';
    let g_seconds = '<?=$room['pause_seconds_at']??'00'?>';

    let g_round_change = false;
    let g_round_pause = <?php if(!empty($room['room_pause_end'])): ?> true <?php else: ?> false <?php endif;  ?>;

    function handle_change_round () {
        g_round_pause = false;

        $.ajax({
            url: '<?=URL?>/api/room.php?change_round=<?=$room['room_id']?>',
            method: 'GET',
            success: (data, status) => {
                data = JSON.parse(data);

                handle_change_status(false);
                room_config.round += 1;
                room_config.work_end = data.data.work_end;
                room_config.pause_start = room_config.work_time;
                room_config.pause_end = null;
                g_minutes = room_config.work_time.split(":")[0];
                g_seconds = room_config.work_time.split(":")[1];
                $('.page-counter h1').text(`${g_minutes}:${g_seconds}`);
                $('#r-round').text(room_config.round);
            }
        });
    }

    
    function handle_round_pause () {
        if (!g_round_pause) {
            g_round_pause = true;

            $.ajax({
                url: '<?=URL?>/api/room.php?pause_round=<?=$room['room_id']?>',
                method: 'GET',
                success: (data, status) => {
                    data = JSON.parse(data);

                    handle_change_status(true);
                    room_config.pause_start = room_config.work_time;
                    room_config.pause_end = data.data.pause_end;
                    g_minutes = room_config.pause_time.split(":")[0];
                    g_seconds = room_config.pause_time.split(":")[1];
                    $('.page-counter h1').text(`${g_minutes}:${g_seconds}`);
                    $('#r-round').text(room_config.round);
                    
                }
            });
        }
    }

    let checked = false;
    
    var coun = setInterval(function() {
        if (!finished && !stopped) {
            
            if (g_round_pause) {

                if (room_config.pause_end == null) {

                    if (checked) {
                        g_round_pause = false;
                        checked = false;
                    } else {
                        checked = true;
                    }

                } else {
                    
                    var now = (new Date()).getTime();
                    var timer_datetime = moment.utc(moment.tz(room_config.pause_end,"<?=TIMEZONE?>")).unix()*1000;
                    var difference = timer_datetime - now;
                    var minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((difference % (1000 * 60)) / 1000);
                    if (minutes < 10) { minutes = "0"+minutes; } 
                    if (seconds < 10) { seconds = "0"+seconds; } 
                    
                    if (difference < 0) {
                        
                        if (room_config.round == room_config.round_limit) {
                            finished = true;
                            $('.page-body').removeClass('room').removeClass('config').addClass('finished');
                            $('.page-counter h1').text("");
                            make_sound();
                        } else {
                            <?php if ($member['member_type'] === 'H'): ?>
                            handle_change_round();
                            <?php else: ?>
                                g_round_pause = false;
                                handle_change_status(false);
                            <?php endif; ?>
                        }
                    } else {
                        g_minutes = minutes;
                        g_seconds = seconds;
                        $('.page-counter h1').text(`${minutes}:${seconds}`);
                    }

                }
                
                
            } else {
                
                if (!awaiting && !paused) {

                    var now = (new Date()).getTime();
                    var timer_datetime = moment.utc(moment.tz(room_config.work_end,"<?=TIMEZONE?>")).unix()*1000;
                    var difference = timer_datetime - now;
                    var minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((difference % (1000 * 60)) / 1000);
                    if (minutes < 10) { minutes = "0"+minutes; } 
                    if (seconds < 10) { seconds = "0"+seconds; } 
                    
                    
                    if (difference < 0) {
    
                        // checking if the round is available in the limit
                        if (room_config.round - 1 < room_config.round_limit) {
                            
                            <?php if ($member['member_type'] === 'H'): ?>
                                // passing request to change round and pause
                                handle_round_pause();
                            <?php else: ?>
                                // pausing the timer
                                room_config.pause_start = room_config.pause_time;
                                g_minutes = room_config.pause_time.split(":")[0];
                                g_seconds = room_config.pause_time.split(":")[1];
                                $('.page-counter h1').text(`${g_minutes}:${g_seconds}`);
                                g_round_pause = true;
                                handle_change_status(true);
                            <?php endif; ?>
    
                        } else {

                            // no rounds available
                            finished = true;
                            $('.page-body').removeClass('room').removeClass('config').addClass('finished');
                            $('.page-counter h1').text("");

                        }


                    } else {
    
                        g_minutes = minutes;
                        g_seconds = seconds;
                        $('.page-counter h1').text(`${minutes}:${seconds}`);
    
                    }
    
                } else if (paused) {
                    $('.page-counter h1').text(room_config.pause_start);  
                }

            }


        }
    }, 1000);



</script>