<?php


namespace app\admin\controller;

use app\service\ChapterService;
use app\model\Book;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;
use app\model\Chapter;

class Chapters extends BaseAdmin
{
    protected $chapterService;

    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->chapterService = app('chapterService');
    }

    public function index()
    {
        $book_id = input('book_id');
        try {
            $book = Book::findOrFail(input('book_id'));
            $data = $this->chapterService->getChapters([
                ['book_id','=',$book_id]
            ]);
            View::assign([
                'chapters' => $data['chapters'],
                'count' => $data['count'],
                'book' => $book
            ]);
            return view();
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }

    public function create(){
        if (request()->isPost()) {
            $data = request()->param();
            $result = Chapter::create($data);
            if ($result){
                $param = [
                    "id" => $data["book_id"],
                    "last_time" => time()
                ];
               $result2 = Book::update($param);
               if ($result2) {
                   return json(['err' =>0,'msg'=>'添加成功']);
               } else {
                   return json(['err' =>1,'msg'=>'添加失败']);
               }
            }else{
                return json(['err' =>1,'msg'=>'添加失败']);
            }
        }
        $book_id = input('book_id');
        $lastChapterOrder = 0;
        $lastChapter = $this->chapterService->getLastChapter($book_id);
        if ($lastChapter){
            $lastChapterOrder = $lastChapter->chapter_order;
        }
        View::assign([
            'book_id' => $book_id,
            'order' => $lastChapterOrder + 1,
        ]);
        return view();
    }

    public function edit()
    {
        if (request()->isPost()) {
            $data = request()->param();
            try {
                $chapter = Chapter::findOrFail($data['id']);
                $result = $chapter->save($data);
                if ($result) {
                    return json(['err' =>0,'msg'=>'修改成功']);
                } else {
                    return json(['err' =>1,'msg'=>'修改失败']);
                }
            } catch (DataNotFoundException $e) {
                abort(404, $e->getMessage());
            } catch (ModelNotFoundException $e) {
                abort(404, $e->getMessage());
            }

        }
        $id = input('id');
        try {
            $chapter = Chapter::findOrFail($id);
            View::assign([
                'chapter' => $chapter,
            ]);
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
        return view();
    }

    public function delete()
    {
        $id = input('id');
        try {
            $chapter = Chapter::findOrFail($id);
            $photos = $chapter->photos;
            if (count($photos) > 0){
                return ['err'=>1,'msg'=>'章节下还存在图片，请先删除'];
            }
            $chapter->delete();
            return ['err'=>0,'msg'=>'删除成功'];
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }

    public function deleteAll(){
        $ids = input('ids');
        Chapter::destroy($ids);
    }

}