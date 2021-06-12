<div class="page-body room">
    
    <div class="page-title room-title">
        <h2><?=$room['room_name']?></h2>
    </div>

    <div class="room-page">
        <div class="page-sub-title">
            <h3>Work</h3>
        </div>
        <div class="page-counter">
            <h1>00:00</h1>
        </div>
        <?php if ($member['member_type'] === 'H'): ?>
        <div class="page-timer-button">
            <button class="btn-submit">Pause</button>
            <div class="timer-reset">
                <button class="reset-btn">Reset</button>
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
                <input type="text" id="work-time-1" value="00" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">:</div>
                <input type="text" id="work-time-2" value="00" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">min</div>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label for="pause-time-1">Pause</label>
            </div>
            <div class="page-input-td time-td">
                <input type="text" id="pause-time-1" value="00" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">:</div>
                <input type="text" id="pause-time-2" value="00" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">min</div>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label for="sound">Sound</label>
            </div>
            <div class="page-input-td">
                <select id="sound">
                    <option value="bip bip bip">Bip Bip Bip</option>
                    <option value="buzzer">Buzzer</option>
                    <option value="dring">Dring</option>
                </select>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label>Round</label>
            </div>
            <div class="page-input-td">
                <div class="room-round">1</div>
            </div>
        </div>

        <div class="page-input-submit">
            <button type="button" class="btn-submit">Start Timer</button>
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

<script>

    let members_online = [];

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

    function get_room_info () {
        $.ajax({
            'url': '<?=URL?>/api/room.php?info=<?=$room['room_id']?>',
            'method': 'GET',
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
                }
            }
        });
    }
    setInterval(() => {
        get_room_info();
    }, 5000);

    get_room_info();
    
    var timer_datetime = new Date("June 14, 2021 15:54:00").getTime();
    var coun = setInterval(function() {
        var now = new Date().getTime();
        var difference = timer_datetime - now;

        var hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        
        $('.page-counter h1').text(`${hours}:${minutes}`);

        if (difference < 0) {
            clearInterval(coun);
            $('.page-body').removeClass('room').removeClass('config').addClass('finished');
        }
    }, 1000);

</script>