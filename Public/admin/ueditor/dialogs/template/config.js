/**
 * Created with JetBrains PhpStorm.
 * User: xuheng
 * Date: 12-8-8
 * Time: 下午2:00
 * To change this template use File | Settings | File Templates.
 */

var templates = [
    {
        "pre":"header_v1.png",
        'title':'大标题-风格1',
        'preHtml':'<div class="heading heading-v1 margin-bottom-40">'+
            '<h2>标题条风格1</h2>'+
            '<p>必賞掲也浦勢温会終見抑杉梗朗義応岡喫国視。一碁渡着台材全書野池霊米辞務担索。図神東積鉄反会米助容出爵浜透欲選令国。它已存活不仅五个世纪，但也跃入电子排版，其余基本保持不变</p>'+
        '</div>',
        "html":'<div class="heading heading-v1 margin-bottom-40">'+
            '<h2>标题条风格</h2>'+
            '<p>必賞掲也浦勢温会終見抑杉梗朗義応岡喫国視。一碁渡着台材全書野池霊米辞務担索。図神東積鉄反会米助容出爵浜透欲選令国。它已存活不仅五个世纪，但也跃入电子排版，其余基本保持不变</p>'+
        '</div>',

    },
    {
        "pre":"header_v2.png",
        'title':'大标题-风格2',
        'preHtml':'<div class="heading heading-v2 margin-bottom-40">'+
            '<h2>标题条风格2</h2>'+
            '<p>必賞掲也浦勢温会終見抑杉梗朗義応岡喫国視。一碁渡着台材全書野池霊米辞務担索。図神東積鉄反会米助容出爵浜透欲選令国。它已存活不仅五个世纪，但也跃入电子排版，其余基本保持不变</p>'+
        '</div>',
        "html":'<div class="heading heading-v2 margin-bottom-40">'+
            '<h2>[这里是标题]</h2>'+
            '<p>必賞掲也浦勢温会終見抑杉梗朗義応岡喫国視。一碁渡着台材全書野池霊米辞務担索。図神東積鉄反会米助容出爵浜透欲選令国。它已存活不仅五个世纪，但也跃入电子排版，其余基本保持不变</p>'+
        '</div>',

    },
    {
        "pre":"header_v3.png",
        'title':'大标题-风格3',
        'preHtml':'<div class="heading heading-v3 margin-bottom-40">'+
            '<h2>标题条风格3</h2>'+
            '<p>必賞掲也浦勢温会終見抑杉梗朗義応岡喫国視。一碁渡着台材全書野池霊米辞務担索。図神東積鉄反会米助容出爵浜透欲選令国。它已存活不仅五个世纪，但也跃入电子排版，其余基本保持不变</p>'+
        '</div>',
        "html":'<div class="heading heading-v3 margin-bottom-40">'+
            '<h2>[这里是标题]</h2>'+
            '<p>必賞掲也浦勢温会終見抑杉梗朗義応岡喫国視。一碁渡着台材全書野池霊米辞務担索。図神東積鉄反会米助容出爵浜透欲選令国。它已存活不仅五个世纪，但也跃入电子排版，其余基本保持不变</p>'+
        '</div>',

    },
    {
        "pre":"col2.png",
        'title':'两列',
        'preHtml':'<div class="row">'+
            '<div class="col-sm-6">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
            '<div class="col-sm-6">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
        '</div>',
        "html":'<div class="row">'+
            '<div class="col-sm-6">'+
                '<h3>[这里是标题]</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
            '<div class="col-sm-6">'+
                '<h3>[这里是标题]</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
        '</div>'

    },
    {
        "pre":"col3.png",
        'title':'三列',
        'preHtml':'<div class="row margin-bottom-30">'+
            '<div class="col-sm-4">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
        '</div>',
        'html':'<div class="row margin-bottom-30">'+
            '<div class="col-sm-4">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<h3>这里是标题</h3>'+
                '<p class="text-justify">転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著舎風。</p>'+
            '</div>'+
        '</div>',

    },
    {
        "pre":"col2-image.png",
        'title':'二列图片',
        'preHtml':'<div class="row">'+
            '<div class="col-sm-6">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-sm-6">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>',
        'html':'<div class="row">'+
            '<div class="col-sm-6">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-sm-6">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>',

    },
    {
        "pre":"col3-image.png",
        'title':'三列图片',
        'preHtml':'<div class="row">'+
            '<div class="col-sm-4">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>',
        'html':'<div class="row">'+
            '<div class="col-sm-4">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-sm-4">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>',

    },
    {
        "pre":"col4-image.png",
        'title':'四列图片',
        'preHtml':'<div class="row">'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>',
        'html':'<div class="row">'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="col-xs-6 col-sm-3">'+
                '<div class="thumbnails">'+
                    '<img class="img-responsive full-width" src="/Public/admin/img/ueditor/img-demo.png" alt="">'+
                    '<div class="caption">'+
                        '<h3>图片标题</h3>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>',

    },
    {
        "pre":"car1.png",
        'title': "幻灯片",
        'preHtml':'<div class="carousel carousel-v2 slide margin-bottom-40" data-ride="carousel" id="%carousel%" data-bs="carousel">'+
                '<ol class="carousel-indicators">'+
                    '<li class="rounded-x" data-target="#%carousel%" data-slide-to="0"></li>'+
                    '<li class="rounded-x active" data-target="#%carousel%" data-slide-to="1"></li>'+
                    '<li class="rounded-x" data-target="#%carousel%" data-slide-to="2"></li>'+
                '</ol>'+

                '<div class="carousel-inner">'+
                    '<div class="item">'+
                        '<img class="full-width img-responsive" src="/Public/admin/img/ueditor/800x450.png" alt="">'+
                    '</div>'+
                    '<div class="item active">'+
                        '<img class="full-width img-responsive" src="/Public/admin/img/ueditor/800x450.png" alt="">'+
                    '</div>'+
                    '<div class="item">'+
                        '<img class="full-width img-responsive" src="/Public/admin/img/ueditor/800x450.png" alt="">'+
                    '</div>'+
                '</div>'+
                '<a class="left carousel-control rounded-x" href="#%carousel%" role="button" data-slide="prev">'+
                    '<i class="fa fa-angle-left arrow-prev"></i>'+
                '</a>'+
                '<a class="right carousel-control rounded-x" href="#%carousel%" role="button" data-slide="next">'+
                    '<i class="fa fa-angle-right arrow-next"></i>'+
                '</a>'+
            '</div>',
        "html":'<div class="carousel carousel-v2 slide margin-bottom-40" data-ride="carousel" id="%carousel%" data-bs="carousel">'+
                '<ol class="carousel-indicators">'+
                    '<li class="rounded-x" data-target="#%carousel%" data-slide-to="0"></li>'+
                    '<li class="rounded-x active" data-target="#%carousel%" data-slide-to="1"></li>'+
                    '<li class="rounded-x" data-target="#%carousel%" data-slide-to="2"></li>'+
                '</ol>'+

                '<div class="carousel-inner">'+
                    '<div class="item">'+
                        '<img class="full-width img-responsive" src="/Public/admin/img/ueditor/800x450.png" alt="">'+
                    '</div>'+
                    '<div class="item active">'+
                        '<img class="full-width img-responsive" src="/Public/admin/img/ueditor/800x450.png" alt="">'+
                    '</div>'+
                    '<div class="item">'+
                        '<img class="full-width img-responsive" src="/Public/admin/img/ueditor/800x450.png" alt="">'+
                    '</div>'+
                '</div>'+

                '<a class="left carousel-control rounded-x" href="#%carousel%" role="button" data-slide="prev">'+
                    '<i class="fa fa-angle-left arrow-prev"></i>'+
                '</a>'+
                '<a class="right carousel-control rounded-x" href="#%carousel%" role="button" data-slide="next">'+
                    '<i class="fa fa-angle-right arrow-next"></i>'+
                '</a>'+
            '</div>'
    },
    {
        "pre":"tab1.png",
        'title':'标签',
        'preHtml':'<div class="tab-v2" data-bs="tab" id="%tab%">'+
            '<ul class="nav nav-tabs">'+
                '<li class="active"><a href="#%tab%-1" data-toggle="tab">标签1</a></li>'+
                '<li><a href="#%tab%-2" data-toggle="tab">标签2</a></li>'+
                '<li><a href="#%tab%-3" data-toggle="tab">标签3</a></li>'+
            '</ul>'+
            '<div class="tab-content">'+
                '<div class="tab-pane fade in active" id="%tab%-1">'+
                    '<h4>标题示例 1</h4>'+
                    '<p>止京断辞大内写案盛的読店況帯昭下百青婦。合先待就記裁衆旅番先育読止吊手行。支指朝体経浦千効和状題年撃込将闘作。速覧上油然万席康名治口大愛一民施。夢止著図治授績信線費児建入基参負記更竹将。単権乗以臨写男定太災堂地載。未記必水鋼職教再申敷曲立更。育応歩仰協玲繰泊私合案型購毎。足要康五冠襲聞旬町角役部運。</p>'+
                '</div>'+
                '<div class="tab-pane fade" id="%tab%-1">'+
                    '<h4>标题示例 2</h4>'+
                    '<p>沖覧航前桐融面台芸郎道化月遂。地園公雪銃里長和申注年点番述血線違考治。続益聞野方朝暴怖上注音公賃陽発研江聞出左。岳承題上友月彼楽率文校住線何康廣程他聞曜。純自済物用利懸回政初販因型。方融満癒新提道新時輪江察。選識得飾申者愛型萩法広暮作製校撤。島噴落行家覧的場情政震能共琉属害滋。明徒作本効没蹊振在広発縮則探壁変無。</p>'+
                '</div>'+
                '<div class="tab-pane fade" id="%tab%-1">'+
                    '<h4>标题示例 3</h4>'+
                    '<p>決毎由覧腹善汗集最将供済週問被有殺件所。遺必連今登裏用透臣損芸仙当全有画音。本仲経揃対商町女易識調殺金鹿続荒立員文推。保絶象法工自約高教年録座戒辺。作早材度暇選戦開九成安聞頻領陽止定愛。着制坂表図算滞新部不周建当発組序移宝行阜。討果物勇活感忘毎校暮導子的見暮格。蔵局美置毎認会陽画第経属頑連付写輪供知通。</p>'+
                '</div>'+
            '</div>'+
        '</div>',
        "html":'<div class="tab-v2" data-bs="tab" id="%tab%">'+
            '<ul class="nav nav-tabs">'+
                '<li class="active"><a href="#%tab%-1" data-toggle="tab">标签1</a></li>'+
                '<li><a href="#%tab%-2" data-toggle="tab">标签2</a></li>'+
                '<li><a href="#%tab%-3" data-toggle="tab">标签3</a></li>'+
            '</ul>'+
            '<div class="tab-content">'+
                '<div class="tab-pane fade in active" id="%tab%-1">'+
                    '<h4>标题示例 1</h4>'+
                    '<p>止京断辞大内写案盛的読店況帯昭下百青婦。合先待就記裁衆旅番先育読止吊手行。支指朝体経浦千効和状題年撃込将闘作。速覧上油然万席康名治口大愛一民施。夢止著図治授績信線費児建入基参負記更竹将。単権乗以臨写男定太災堂地載。未記必水鋼職教再申敷曲立更。育応歩仰協玲繰泊私合案型購毎。足要康五冠襲聞旬町角役部運。</p>'+
                '</div>'+
                '<div class="tab-pane fade" id="%tab%-2">'+
                    '<h4>标题示例 2</h4>'+
                    '<p>沖覧航前桐融面台芸郎道化月遂。地園公雪銃里長和申注年点番述血線違考治。続益聞野方朝暴怖上注音公賃陽発研江聞出左。岳承題上友月彼楽率文校住線何康廣程他聞曜。純自済物用利懸回政初販因型。方融満癒新提道新時輪江察。選識得飾申者愛型萩法広暮作製校撤。島噴落行家覧的場情政震能共琉属害滋。明徒作本効没蹊振在広発縮則探壁変無。</p>'+
                '</div>'+
                '<div class="tab-pane fade" id="%tab%-3">'+
                    '<h4>标题示例 3</h4>'+
                    '<p>決毎由覧腹善汗集最将供済週問被有殺件所。遺必連今登裏用透臣損芸仙当全有画音。本仲経揃対商町女易識調殺金鹿続荒立員文推。保絶象法工自約高教年録座戒辺。作早材度暇選戦開九成安聞頻領陽止定愛。着制坂表図算滞新部不周建当発組序移宝行阜。討果物勇活感忘毎校暮導子的見暮格。蔵局美置毎認会陽画第経属頑連付写輪供知通。</p>'+
                '</div>'+
            '</div>'+
        '</div>'
    },
    {
        "pre":"acc1.png",
        'title':'手风琴',
        'preHtml':'<div class="panel-group acc-v1" id="%accordion%-parent" data-bs="collapse">'+
            '<div class="panel panel-default">'+
                '<div class="panel-heading">'+
                    '<h4 class="panel-title">'+
                        '<a class="accordion-toggle" data-toggle="collapse" data-parent="#%accordion%-parent" href="#%accordion%-1" aria-expanded="true">'+
                            '手风琴＃1'+
                        '</a>'+
                    '</h4>'+
                '</div>'+
                '<div id="%accordion%-1" class="panel-collapse collapse in" aria-expanded="true">'+
                    '<div class="panel-body">'+
                        '<p>'+
                            '転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著助駐措件注舎風。理摘団稚容販長図整岐賞見労。打必了金海矢映目責男光政係死倒供年補。親万微晴提趣再東今目送同京。活等止示姿今員報杯報必理群究真射会前一。'+
                        '</p>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="panel panel-default">'+
                '<div class="panel-heading">'+
                    '<h4 class="panel-title">'+
                        '<a class="accordion-toggle" data-toggle="collapse" data-parent="#%accordion%-parent" href="#%accordion%-2" aria-expanded="true">'+
                            '手风琴＃2'+
                        '</a>'+
                    '</h4>'+
                '</div>'+
                '<div id="%accordion%-2" class="panel-collapse collapse" aria-expanded="true">'+
                    '<div class="panel-body">'+
                        '<p>'+
                            '転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著助駐措件注舎風。理摘団稚容販長図整岐賞見労。打必了金海矢映目責男光政係死倒供年補。親万微晴提趣再東今目送同京。活等止示姿今員報杯報必理群究真射会前一。'+
                        '</p>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="panel panel-default">'+
                '<div class="panel-heading">'+
                    '<h4 class="panel-title">'+
                        '<a class="accordion-toggle" data-toggle="collapse" data-parent="#%accordion%-parent" href="#%accordion%-3" aria-expanded="true">'+
                            '手风琴＃3'+
                        '</a>'+
                    '</h4>'+
                '</div>'+
                '<div id="%accordion%-3" class="panel-collapse collapse" aria-expanded="true">'+
                    '<div class="panel-body">'+
                        '<p>'+
                            '転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著助駐措件注舎風。理摘団稚容販長図整岐賞見労。打必了金海矢映目責男光政係死倒供年補。親万微晴提趣再東今目送同京。活等止示姿今員報杯報必理群究真射会前一。'+
                        '</p>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            
        '</div>',
        'html':'<div class="panel-group acc-v1" id="%accordion%-parent" data-bs="collapse">'+
            '<div class="panel panel-default">'+
                '<div class="panel-heading">'+
                    '<h4 class="panel-title">'+
                        '<a class="accordion-toggle" data-toggle="collapse" data-parent="#%accordion%-parent" href="#%accordion%-1" aria-expanded="true">'+
                            '手风琴＃1'+
                        '</a>'+
                    '</h4>'+
                '</div>'+
                '<div id="%accordion%-1" class="panel-collapse collapse in" aria-expanded="true">'+
                    '<div class="panel-body">'+
                        '<p>'+
                            '転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著助駐措件注舎風。理摘団稚容販長図整岐賞見労。打必了金海矢映目責男光政係死倒供年補。親万微晴提趣再東今目送同京。活等止示姿今員報杯報必理群究真射会前一。'+
                        '</p>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="panel panel-default">'+
                '<div class="panel-heading">'+
                    '<h4 class="panel-title">'+
                        '<a class="accordion-toggle" data-toggle="collapse" data-parent="#%accordion%-parent" href="#%accordion%-2" aria-expanded="true">'+
                            '手风琴＃2'+
                        '</a>'+
                    '</h4>'+
                '</div>'+
                '<div id="%accordion%-2" class="panel-collapse collapse" aria-expanded="true">'+
                    '<div class="panel-body">'+
                        '<p>'+
                            '転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著助駐措件注舎風。理摘団稚容販長図整岐賞見労。打必了金海矢映目責男光政係死倒供年補。親万微晴提趣再東今目送同京。活等止示姿今員報杯報必理群究真射会前一。'+
                        '</p>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="panel panel-default">'+
                '<div class="panel-heading">'+
                    '<h4 class="panel-title">'+
                        '<a class="accordion-toggle" data-toggle="collapse" data-parent="#%accordion%-parent" href="#%accordion%-3" aria-expanded="true">'+
                            '手风琴＃3'+
                        '</a>'+
                    '</h4>'+
                '</div>'+
                '<div id="%accordion%-3" class="panel-collapse collapse" aria-expanded="true">'+
                    '<div class="panel-body">'+
                        '<p>'+
                            '転中難介贅活念暮注家救育務応見。市知機指載退石政士決想軽絶税世継街由。徒近姿反本入押討限絶当括戸川聞保白月禁題。毎聞迅着責銀対書月表告結言権。聞式質聴進説思氷実期媛岸著助駐措件注舎風。理摘団稚容販長図整岐賞見労。打必了金海矢映目責男光政係死倒供年補。親万微晴提趣再東今目送同京。活等止示姿今員報杯報必理群究真射会前一。'+
                        '</p>'+
                    '</div>'+
                '</div>'+
            '</div>'+            
        '</div>'
    }
];